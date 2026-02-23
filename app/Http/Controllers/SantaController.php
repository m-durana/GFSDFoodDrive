<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Child;
use App\Models\DismissedDuplicate;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\SchoolRange;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SantaController extends Controller
{
    public function index(): View
    {
        return view('santa.index');
    }

    public function allFamilies(): View
    {
        return view('santa.families', [
            'families' => Family::with('user')->orderBy('family_number')->get(),
        ]);
    }

    public function numberAssignment(): View
    {
        $schoolRanges = SchoolRange::orderBy('sort_order')->get();

        // Get families without numbers, eager load children
        $unassignedFamilies = Family::whereNull('family_number')
            ->with('children')
            ->get();

        // Group families by oldest child's school
        $grouped = [];
        $noSchool = [];

        foreach ($unassignedFamilies as $family) {
            $oldestChild = $family->children->sortByDesc(function ($child) {
                return (int) $child->age;
            })->first();

            if ($oldestChild && $oldestChild->school) {
                $school = $oldestChild->school;
                $grouped[$school][] = $family;
            } else {
                $noSchool[] = $family;
            }
        }

        // Get next available number per school range
        $rangeInfo = [];
        foreach ($schoolRanges as $range) {
            $rangeInfo[$range->school_name] = [
                'range' => $range,
                'next' => $range->nextAvailableNumber(),
            ];
        }

        // Already assigned families count
        $assignedCount = Family::whereNotNull('family_number')->count();

        return view('santa.number-assignment', compact(
            'schoolRanges', 'grouped', 'noSchool', 'rangeInfo', 'assignedCount'
        ));
    }

    public function updateFamilyNumber(Request $request): RedirectResponse
    {
        $request->validate([
            'family_id' => ['required', 'exists:families,id'],
            'family_number' => ['required', 'integer', 'min:1'],
        ]);

        $family = Family::findOrFail($request->family_id);

        // Check uniqueness
        $existing = Family::where('family_number', $request->family_number)
            ->where('id', '!=', $family->id)
            ->first();

        if ($existing) {
            return redirect()->route('santa.numberAssignment')
                ->with('error', "Number {$request->family_number} is already assigned to {$existing->family_name}.");
        }

        $family->update(['family_number' => $request->family_number]);

        return redirect()->route('santa.numberAssignment')
            ->with('success', "Family '{$family->family_name}' assigned number {$request->family_number}.");
    }

    public function autoAssign(): RedirectResponse
    {
        $unassigned = Family::whereNull('family_number')->with('children')->get();
        $schoolRanges = SchoolRange::orderBy('sort_order')->get();
        $assigned = 0;
        $errors = [];

        foreach ($unassigned as $family) {
            $oldestChild = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();
            $school = $oldestChild?->school;

            if (!$school) {
                $errors[] = "{$family->family_name}: no children or no school set";
                continue;
            }

            // Find matching range
            $range = $schoolRanges->first(function ($r) use ($school) {
                return stripos($school, $r->school_name) !== false
                    || stripos($r->school_name, $school) !== false;
            });

            if (!$range) {
                // Fall back to Special Case range
                $range = $schoolRanges->firstWhere('school_name', 'Special Case');
            }

            if (!$range) {
                $errors[] = "{$family->family_name}: no matching school range for '{$school}'";
                continue;
            }

            $nextNumber = $range->nextAvailableNumber();
            if ($nextNumber === null) {
                $errors[] = "{$family->family_name}: range for '{$range->school_name}' is full";
                continue;
            }

            $family->update(['family_number' => $nextNumber]);
            $assigned++;
        }

        $message = "Auto-assigned {$assigned} families.";
        if (count($errors) > 0) {
            $message .= ' Skipped ' . count($errors) . ': ' . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= '...';
            }
        }

        return redirect()->route('santa.numberAssignment')->with('success', $message);
    }

    public function schoolRanges(): View
    {
        return view('santa.school-ranges', [
            'ranges' => SchoolRange::orderBy('sort_order')->get(),
        ]);
    }

    public function storeSchoolRange(Request $request): RedirectResponse
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'range_start' => ['required', 'integer', 'min:0'],
            'range_end' => ['required', 'integer', 'min:0', 'gt:range_start'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        SchoolRange::create([
            'school_name' => $request->school_name,
            'range_start' => $request->range_start,
            'range_end' => $request->range_end,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$request->school_name}' added.");
    }

    public function updateSchoolRange(Request $request, SchoolRange $schoolRange): RedirectResponse
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'range_start' => ['required', 'integer', 'min:0'],
            'range_end' => ['required', 'integer', 'min:0', 'gt:range_start'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $schoolRange->update($request->only('school_name', 'range_start', 'range_end', 'sort_order'));

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$schoolRange->school_name}' updated.");
    }

    public function destroySchoolRange(SchoolRange $schoolRange): RedirectResponse
    {
        $name = $schoolRange->school_name;
        $schoolRange->delete();

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$name}' removed.");
    }

    public function settings(): View
    {
        return view('santa.settings', [
            'selfRegistration' => Setting::get('self_registration_enabled', '0') === '1',
            'seasonYear' => Setting::get('season_year', (string) date('Y')),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        Setting::set('self_registration_enabled', $request->boolean('self_registration_enabled') ? '1' : '0');
        Setting::set('season_year', $request->input('season_year', (string) date('Y')));

        // Google OAuth settings
        if ($request->filled('google_client_id')) {
            Setting::set('google_client_id', $request->input('google_client_id'));
        }
        if ($request->filled('google_client_secret')) {
            Setting::set('google_client_secret', $request->input('google_client_secret'));
        }
        // Allow clearing OAuth settings
        if ($request->has('google_client_id') && !$request->filled('google_client_id')) {
            Setting::where('key', 'google_client_id')->delete();
            Setting::where('key', 'google_client_secret')->delete();
        }

        return redirect()->route('santa.settings')
            ->with('success', 'Settings updated successfully.');
    }

    public function gifts(Request $request): View
    {
        $query = Child::with('family')->whereHas('family');

        // Filter by gift level
        if ($request->filled('level')) {
            $level = GiftLevel::tryFrom((int) $request->level);
            if ($level !== null) {
                $query->where('gift_level', $level->value);
            }
        }

        // Filter by mail merge status
        if ($request->filled('merged')) {
            $query->where('mail_merged', $request->merged === '1');
        }

        // Filter by adopter status
        if ($request->filled('adopted')) {
            if ($request->adopted === '1') {
                $query->whereNotNull('adopter_name')->where('adopter_name', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('adopter_name')->orWhere('adopter_name', '');
                });
            }
        }

        $children = $query->orderBy('family_id')->get();

        // Summary counts
        $allChildren = Child::whereHas('family');
        $counts = [
            'total' => (clone $allChildren)->count(),
            'no_gifts' => (clone $allChildren)->where('gift_level', GiftLevel::None->value)->count(),
            'partial' => (clone $allChildren)->whereIn('gift_level', [GiftLevel::Partial->value, GiftLevel::Moderate->value])->count(),
            'complete' => (clone $allChildren)->where('gift_level', GiftLevel::Full->value)->count(),
            'unmerged' => (clone $allChildren)->where('mail_merged', false)->count(),
        ];

        return view('santa.gifts', compact('children', 'counts'));
    }

    public function volunteers(): View
    {
        $volunteers = User::where('permission', '>=', 7)
            ->where('permission', '<=', 9)
            ->orderBy('first_name')
            ->get();

        $unassignedFamilies = Family::whereNull('volunteer_id')
            ->whereNotNull('family_number')
            ->orderBy('family_number')
            ->get();

        $assignments = [];
        foreach ($volunteers as $volunteer) {
            $assignments[$volunteer->id] = Family::where('volunteer_id', $volunteer->id)
                ->with('children')
                ->orderBy('family_number')
                ->get();
        }

        return view('santa.volunteers', compact('volunteers', 'unassignedFamilies', 'assignments'));
    }

    public function assignVolunteer(Request $request): RedirectResponse
    {
        $request->validate([
            'family_id' => ['required', 'exists:families,id'],
            'volunteer_id' => ['required', 'exists:users,id'],
        ]);

        Family::findOrFail($request->family_id)->update(['volunteer_id' => $request->volunteer_id]);

        return redirect()->route('santa.volunteers')
            ->with('success', 'Family assigned to volunteer.');
    }

    public function unassignVolunteer(Family $family): RedirectResponse
    {
        $family->update(['volunteer_id' => null]);

        return redirect()->route('santa.volunteers')
            ->with('success', "Family '{$family->family_name}' unassigned.");
    }

    public function volunteerList(User $user)
    {
        $families = Family::where('volunteer_id', $user->id)
            ->with('children')
            ->orderBy('family_number')
            ->get();

        $volunteerName = $user->first_name . ' ' . $user->last_name;

        return view('documents.volunteer-list', compact('families', 'volunteerName'));
    }

    public function exportFamilies(Request $request)
    {
        $query = Family::with('children');

        // Apply filters
        if ($request->filled('needs_baby')) {
            $query->where('needs_baby_supplies', true);
        }
        if ($request->filled('severe_need')) {
            $query->whereNotNull('severe_need')->where('severe_need', '!=', '');
        }
        if ($request->filled('school')) {
            $school = $request->school;
            $query->whereHas('children', fn($q) => $q->where('school', $school));
        }
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->filled('language')) {
            $query->where('preferred_language', $request->language);
        }
        if ($request->filled('assigned')) {
            if ($request->assigned === '1') {
                $query->whereNotNull('family_number');
            } else {
                $query->whereNull('family_number');
            }
        }
        if ($request->filled('done')) {
            $query->where('family_done', $request->done === '1');
        }
        if ($request->filled('gift_level')) {
            $level = (int) $request->gift_level;
            $query->whereHas('children', fn($q) => $q->where('gift_level', $level));
        }

        $families = $query->orderBy('family_number')->get();

        // If export=csv, return CSV
        if ($request->get('format') === 'csv') {
            $filename = 'families-export-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($families) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['#', 'Family Name', 'Address', 'Phone', 'Alt Phone', 'Email', 'Language', 'Adults', 'Children', 'Total', 'Delivery Pref', 'Delivery Date', 'Team', 'Status', 'Done', 'Baby Supplies', 'Severe Need']);
                foreach ($families as $f) {
                    fputcsv($out, [
                        $f->family_number,
                        $f->family_name,
                        $f->address,
                        $f->phone1,
                        $f->phone2,
                        $f->email,
                        $f->preferred_language,
                        $f->number_of_adults,
                        $f->number_of_children,
                        $f->number_of_family_members,
                        $f->delivery_preference,
                        $f->delivery_date,
                        $f->delivery_team,
                        $f->delivery_status?->label() ?? 'N/A',
                        $f->family_done ? 'Yes' : 'No',
                        $f->needs_baby_supplies ? 'Yes' : 'No',
                        $f->severe_need ? 'Yes' : 'No',
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        // If export=children-csv, return children CSV
        if ($request->get('format') === 'children-csv') {
            $filename = 'children-export-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($families) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Family #', 'Family Name', 'Gender', 'Age', 'School', 'Sizes', 'Clothing', 'Styles', 'Toy Ideas', 'Gift Level', 'Gifts Received', 'Adopter', 'Tag Printed', 'Where is Tag']);
                foreach ($families as $f) {
                    foreach ($f->children as $c) {
                        fputcsv($out, [
                            $f->family_number,
                            $f->family_name,
                            $c->gender,
                            $c->age,
                            $c->school,
                            $c->all_sizes,
                            $c->clothing_options,
                            $c->clothing_styles,
                            $c->toy_ideas,
                            $c->gift_level?->label() ?? 'None',
                            $c->gifts_received,
                            $c->adopter_name,
                            $c->mail_merged ? 'Yes' : 'No',
                            $c->where_is_tag,
                        ]);
                    }
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Get filter options for dropdowns
        $schools = Child::select('school')->distinct()->whereNotNull('school')->where('school', '!=', '')->orderBy('school')->pluck('school');
        $languages = Family::select('preferred_language')->distinct()->whereNotNull('preferred_language')->orderBy('preferred_language')->pluck('preferred_language');
        $teams = Family::select('delivery_team')->distinct()->whereNotNull('delivery_team')->where('delivery_team', '!=', '')->orderBy('delivery_team')->pluck('delivery_team');

        return view('santa.export', compact('families', 'schools', 'languages', 'teams'));
    }

    public function reports(): View
    {
        $totalFamilies = Family::count();
        $assignedFamilies = Family::whereNotNull('family_number')->count();
        $totalChildren = Child::count();

        // Gift level breakdown
        $giftLevels = [
            'none' => Child::where('gift_level', GiftLevel::None->value)->count(),
            'partial' => Child::where('gift_level', GiftLevel::Partial->value)->count(),
            'moderate' => Child::where('gift_level', GiftLevel::Moderate->value)->count(),
            'full' => Child::where('gift_level', GiftLevel::Full->value)->count(),
        ];

        // Age group breakdown
        $ageGroups = [
            'infants' => Family::sum('infants'),
            'young_children' => Family::sum('young_children'),
            'children' => Family::sum('children_count'),
            'tweens' => Family::sum('tweens'),
            'teenagers' => Family::sum('teenagers'),
        ];

        // Delivery status
        $deliveryStats = [
            'pending' => Family::where('delivery_status', 'pending')->orWhereNull('delivery_status')->count(),
            'in_transit' => Family::where('delivery_status', 'in_transit')->count(),
            'delivered' => Family::where('delivery_status', 'delivered')->count(),
            'picked_up' => Family::where('delivery_status', 'picked_up')->count(),
        ];

        // Families done
        $familiesDone = Family::where('family_done', true)->count();

        // Children by school
        $childrenBySchool = Child::select('school')
            ->selectRaw('count(*) as total')
            ->whereNotNull('school')
            ->where('school', '!=', '')
            ->groupBy('school')
            ->orderByDesc('total')
            ->get();

        // Language breakdown
        $languages = Family::select('preferred_language')
            ->selectRaw('count(*) as total')
            ->whereNotNull('preferred_language')
            ->groupBy('preferred_language')
            ->orderByDesc('total')
            ->get();

        // Tag/merge stats
        $tagStats = [
            'merged' => Child::where('mail_merged', true)->count(),
            'unmerged' => Child::where('mail_merged', false)->count(),
            'adopted' => Child::whereNotNull('adopter_name')->where('adopter_name', '!=', '')->count(),
        ];

        // Needs
        $needsStats = [
            'baby_supplies' => Family::where('needs_baby_supplies', true)->count(),
            'severe_need' => Family::whereNotNull('severe_need')->where('severe_need', '!=', '')->count(),
        ];

        return view('santa.reports', compact(
            'totalFamilies', 'assignedFamilies', 'totalChildren',
            'giftLevels', 'ageGroups', 'deliveryStats', 'familiesDone',
            'childrenBySchool', 'languages', 'tagStats', 'needsStats'
        ));
    }

    public function shoppingList(Request $request)
    {
        $groceryItems = GroceryItem::orderBy('sort_order')->get();

        // If managing items (no family filter), show the formula editor
        if ($request->get('manage') === '1') {
            return view('santa.shopping-manage', compact('groceryItems'));
        }

        // Build family query
        $query = Family::whereNotNull('family_number')->with('children')->orderBy('family_number');

        if ($request->filled('family_number_start') && $request->filled('family_number_end')) {
            $query->whereBetween('family_number', [$request->family_number_start, $request->family_number_end]);
        } elseif ($request->filled('family_id')) {
            $query->where('id', $request->family_id);
        }

        $families = $query->get();

        // Calculate shopping list per family
        $shoppingLists = [];
        $totals = []; // aggregate totals across all families
        foreach ($families as $family) {
            $list = GroceryItem::calculateForFamily($family);
            $shoppingLists[$family->id] = $list;
            foreach ($list as $itemName => $info) {
                $totals[$itemName] = ($totals[$itemName] ?? 0) + $info['quantity'];
            }
        }

        // CSV export — aggregate totals
        if ($request->get('format') === 'csv') {
            $filename = 'shopping-list-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($families, $shoppingLists, $groceryItems, $totals) {
                $out = fopen('php://output', 'w');

                // Header row: Family Number, Family Name, Members, then each item
                $headerRow = ['Family Number', 'Family Name', 'Members'];
                foreach ($groceryItems as $item) {
                    $headerRow[] = $item->name;
                }
                $headerRow[] = 'Total';
                fputcsv($out, $headerRow);

                // Per-family rows
                foreach ($families as $family) {
                    $row = [$family->family_number, $family->family_name, $family->number_of_family_members];
                    $familyTotal = 0;
                    foreach ($groceryItems as $item) {
                        $qty = $shoppingLists[$family->id][$item->name]['quantity'] ?? 0;
                        $row[] = $qty;
                        $familyTotal += $qty;
                    }
                    $row[] = $familyTotal;
                    fputcsv($out, $row);
                }

                // Totals row
                $totalRow = ['', 'TOTALS', ''];
                $grandTotal = 0;
                foreach ($groceryItems as $item) {
                    $qty = $totals[$item->name] ?? 0;
                    $totalRow[] = $qty;
                    $grandTotal += $qty;
                }
                $totalRow[] = $grandTotal;
                fputcsv($out, $totalRow);

                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Get all families for filter dropdown
        $allFamilies = Family::whereNotNull('family_number')->orderBy('family_number')
            ->select('id', 'family_number', 'family_name', 'number_of_family_members')
            ->get();

        return view('santa.shopping-list', compact(
            'families', 'shoppingLists', 'totals', 'groceryItems', 'allFamilies'
        ));
    }

    public function updateGroceryItem(Request $request, GroceryItem $groceryItem): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:canned,dry,personal,condiment'],
            'qty_1' => ['required', 'integer', 'min:0'],
            'qty_2' => ['required', 'integer', 'min:0'],
            'qty_3' => ['required', 'integer', 'min:0'],
            'qty_4' => ['required', 'integer', 'min:0'],
            'qty_5' => ['required', 'integer', 'min:0'],
            'qty_6' => ['required', 'integer', 'min:0'],
            'qty_7' => ['required', 'integer', 'min:0'],
            'qty_8' => ['required', 'integer', 'min:0'],
        ]);

        $groceryItem->update($request->only([
            'name', 'category',
            'qty_1', 'qty_2', 'qty_3', 'qty_4',
            'qty_5', 'qty_6', 'qty_7', 'qty_8',
        ]));

        return redirect()->route('santa.shoppingList', ['manage' => '1'])
            ->with('success', "Item '{$groceryItem->name}' updated.");
    }

    public function storeGroceryItem(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:canned,dry,personal,condiment'],
        ]);

        $maxOrder = GroceryItem::max('sort_order') ?? 0;

        GroceryItem::create([
            'name' => $request->name,
            'category' => $request->category,
            'qty_1' => $request->integer('qty_1', 0),
            'qty_2' => $request->integer('qty_2', 0),
            'qty_3' => $request->integer('qty_3', 0),
            'qty_4' => $request->integer('qty_4', 0),
            'qty_5' => $request->integer('qty_5', 0),
            'qty_6' => $request->integer('qty_6', 0),
            'qty_7' => $request->integer('qty_7', 0),
            'qty_8' => $request->integer('qty_8', 0),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('santa.shoppingList', ['manage' => '1'])
            ->with('success', "Item '{$request->name}' added.");
    }

    public function destroyGroceryItem(GroceryItem $groceryItem): RedirectResponse
    {
        $name = $groceryItem->name;
        $groceryItem->delete();

        return redirect()->route('santa.shoppingList', ['manage' => '1'])
            ->with('success', "Item '{$name}' removed.");
    }

    public function importGroceryItems(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->route('santa.shoppingList', ['manage' => '1'])
                ->with('error', 'Could not open CSV file.');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header || count($header) < 12) {
            fclose($handle);
            return redirect()->route('santa.shoppingList', ['manage' => '1'])
                ->with('error', 'Invalid CSV format. Expected header with Family Number, demographics, and item columns.');
        }

        // Item columns start at index 12 (after: Family Number, Number of Children, Number of Adults,
        // Female Adults, Male Adults, Infants, Young Child, Child, Tween, Teenager, Number of Family Members, Total)
        $itemNames = array_slice($header, 12);
        // Remove "Real Total" and "Additional Items" if present
        $itemNames = array_filter($itemNames, fn($name) => !in_array(trim($name, '" '), ['Real Total', 'Additional Items', 'Total', '']));
        $itemNames = array_values($itemNames);

        // Read all data rows, group by family size
        $dataBySize = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 12) continue;
            $size = max(1, min(8, (int) $row[10])); // Number of Family Members col
            $quantities = array_slice($row, 12, count($itemNames));
            if (!isset($dataBySize[$size])) {
                $dataBySize[$size] = [];
            }
            $dataBySize[$size][] = $quantities;
        }
        fclose($handle);

        // For each family size, take the MEDIAN quantity per item (handles outliers better than mode)
        $formulaBySize = [];
        for ($size = 1; $size <= 8; $size++) {
            if (!isset($dataBySize[$size]) || empty($dataBySize[$size])) continue;
            $rows = $dataBySize[$size];
            for ($i = 0; $i < count($itemNames); $i++) {
                $values = array_map(fn($r) => (int) ($r[$i] ?? 0), $rows);
                sort($values);
                $mid = intdiv(count($values), 2);
                $median = count($values) % 2 === 0
                    ? (int) round(($values[$mid - 1] + $values[$mid]) / 2)
                    : $values[$mid];
                $formulaBySize[$size][$i] = $median;
            }
        }

        // Upsert grocery items
        $updated = 0;
        $created = 0;
        foreach ($itemNames as $idx => $rawName) {
            $name = trim($rawName, '" ');
            if (empty($name)) continue;

            $data = [
                'qty_1' => $formulaBySize[1][$idx] ?? 0,
                'qty_2' => $formulaBySize[2][$idx] ?? 0,
                'qty_3' => $formulaBySize[3][$idx] ?? 0,
                'qty_4' => $formulaBySize[4][$idx] ?? 0,
                'qty_5' => $formulaBySize[5][$idx] ?? 0,
                'qty_6' => $formulaBySize[6][$idx] ?? 0,
                'qty_7' => $formulaBySize[7][$idx] ?? 0,
                'qty_8' => $formulaBySize[8][$idx] ?? 0,
            ];

            $existing = GroceryItem::where('name', $name)->first();
            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                $category = $this->guessCategory($name);
                GroceryItem::create(array_merge($data, [
                    'name' => $name,
                    'category' => $category,
                    'sort_order' => GroceryItem::max('sort_order') + 1,
                ]));
                $created++;
            }
        }

        return redirect()->route('santa.shoppingList', ['manage' => '1'])
            ->with('success', "Imported: {$updated} items updated, {$created} new items created from " . count($dataBySize) . " family size groups.");
    }

    public function exportGroceryFormula()
    {
        $items = GroceryItem::orderBy('sort_order')->get();
        $filename = 'grocery-formula-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Category', 'Size 1', 'Size 2', 'Size 3', 'Size 4', 'Size 5', 'Size 6', 'Size 7', 'Size 8', 'Conditional', 'Condition Field']);
            foreach ($items as $item) {
                fputcsv($out, [
                    $item->name, $item->category,
                    $item->qty_1, $item->qty_2, $item->qty_3, $item->qty_4,
                    $item->qty_5, $item->qty_6, $item->qty_7, $item->qty_8,
                    $item->conditional ? 'Yes' : 'No',
                    $item->condition_field,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function duplicates(): View
    {
        $families = Family::with('children')->get();
        $pairs = [];

        for ($i = 0; $i < $families->count(); $i++) {
            for ($j = $i + 1; $j < $families->count(); $j++) {
                $a = $families[$i];
                $b = $families[$j];

                $score = $this->duplicateScore($a, $b);
                if ($score >= 3) {
                    // Check if dismissed
                    if (!DismissedDuplicate::isDismissed($a->id, $b->id)) {
                        $pairs[] = [
                            'family_a' => $a,
                            'family_b' => $b,
                            'score' => $score,
                        ];
                    }
                }
            }
        }

        // Sort by score descending
        usort($pairs, fn($a, $b) => $b['score'] <=> $a['score']);

        return view('santa.duplicates', compact('pairs'));
    }

    public function dismissDuplicate(Request $request): RedirectResponse
    {
        $request->validate([
            'family_a_id' => ['required', 'exists:families,id'],
            'family_b_id' => ['required', 'exists:families,id'],
        ]);

        DismissedDuplicate::dismiss($request->family_a_id, $request->family_b_id);

        return redirect()->route('santa.duplicates')
            ->with('success', 'Pair dismissed as not duplicates.');
    }

    public function mergeFamilies(Request $request): RedirectResponse
    {
        $request->validate([
            'keep_id' => ['required', 'exists:families,id'],
            'merge_id' => ['required', 'exists:families,id', 'different:keep_id'],
        ]);

        $keep = Family::findOrFail($request->keep_id);
        $merge = Family::findOrFail($request->merge_id);

        // Move all children from merge to keep
        Child::where('family_id', $merge->id)->update(['family_id' => $keep->id]);

        // Delete dismissed duplicates for the merged family
        DismissedDuplicate::where('family_a_id', $merge->id)
            ->orWhere('family_b_id', $merge->id)
            ->delete();

        $mergeName = $merge->family_name;
        $merge->delete();

        return redirect()->route('santa.duplicates')
            ->with('success', "Family '{$mergeName}' merged into '{$keep->family_name}'. Children transferred.");
    }

    public function geocodeFamilies(): RedirectResponse
    {
        $families = Family::whereNull('latitude')
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->get();

        $geocoded = 0;
        $errors = 0;

        foreach ($families as $family) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'GFSDFoodDrive/1.0',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $family->address,
                    'format' => 'json',
                    'limit' => 1,
                ]);

                if ($response->successful() && count($response->json()) > 0) {
                    $result = $response->json()[0];
                    $family->update([
                        'latitude' => $result['lat'],
                        'longitude' => $result['lon'],
                    ]);
                    $geocoded++;
                } else {
                    $errors++;
                }

                // Respect Nominatim rate limit (1 req/sec)
                usleep(1100000);
            } catch (\Exception $e) {
                $errors++;
            }
        }

        return redirect()->route('santa.settings')
            ->with('success', "Geocoded {$geocoded} families. {$errors} could not be geocoded.");
    }

    private function duplicateScore(Family $a, Family $b): int
    {
        $score = 0;

        // Family name similarity (Levenshtein)
        if ($a->family_name && $b->family_name) {
            $maxLen = max(strlen($a->family_name), strlen($b->family_name));
            if ($maxLen > 0) {
                $distance = levenshtein(
                    strtolower(trim($a->family_name)),
                    strtolower(trim($b->family_name))
                );
                $similarity = 1 - ($distance / $maxLen);
                if ($similarity > 0.8) {
                    $score += 3;
                }
            }
        }

        // Address match (normalized)
        if ($a->address && $b->address) {
            $addrA = strtolower(preg_replace('/[^a-z0-9]/', '', $a->address));
            $addrB = strtolower(preg_replace('/[^a-z0-9]/', '', $b->address));
            if ($addrA === $addrB && strlen($addrA) > 5) {
                $score += 3;
            }
        }

        // Phone match (digits only)
        $phonesA = array_filter([
            preg_replace('/\D/', '', $a->phone1 ?? ''),
            preg_replace('/\D/', '', $a->phone2 ?? ''),
        ], fn($p) => strlen($p) >= 7);

        $phonesB = array_filter([
            preg_replace('/\D/', '', $b->phone1 ?? ''),
            preg_replace('/\D/', '', $b->phone2 ?? ''),
        ], fn($p) => strlen($p) >= 7);

        foreach ($phonesA as $pA) {
            foreach ($phonesB as $pB) {
                if ($pA === $pB) {
                    $score += 2;
                } elseif (substr($pA, -4) === substr($pB, -4)) {
                    $score += 1;
                }
            }
        }

        return $score;
    }

    private function guessCategory(string $name): string
    {
        $name = Str::lower($name);
        $personal = ['shampoo', 'soap', 'toothbrush', 'toothpaste', 'deodorant', 'diaper', 'baby food', 'toilet', 'paper towel', 'conditioner', 'mouthwash', 'feminine', 'dog food', 'cat food', 'personal'];
        $condiment = ['sauce', 'ketchup', 'mustard', 'mayo', 'oil', 'salt', 'season', 'honey', 'jam', 'jelly', 'juice', 'coffee', 'tea', 'cocoa', 'soda', 'water', 'peanut butter', 'popcorn', 'nuts', 'cookie', 'salad dressing', 'bbq', 'gravy', 'potato'];
        $dry = ['cereal', 'pasta', 'ramen', 'rice', 'flour', 'sugar', 'oatmeal', 'crackers', 'chips', 'bread', 'mix', 'helper', 'noodle', 'snack', 'granola', 'marshmallow', 'stuffing', 'bean', 'baking', 'powdered'];

        foreach ($personal as $word) { if (str_contains($name, $word)) return 'personal'; }
        foreach ($condiment as $word) { if (str_contains($name, $word)) return 'condiment'; }
        foreach ($dry as $word) { if (str_contains($name, $word)) return 'dry'; }
        return 'canned';
    }

    public function shoppingDay(): View
    {
        $assignments = ShoppingAssignment::with('user')->get();
        $coordinators = User::where('permission', '>=', 8)->orderBy('first_name')->get();

        // Get all grocery categories
        $allCategories = GroceryItem::select('category')->distinct()->pluck('category')->toArray();

        // Determine which categories/family ranges are already assigned
        $assignedCategories = [];
        $assignedRanges = [];
        foreach ($assignments as $a) {
            if ($a->split_type === 'category') {
                $assignedCategories = array_merge($assignedCategories, $a->categories ?? []);
            } else {
                $assignedRanges[] = ['start' => $a->family_start, 'end' => $a->family_end];
            }
        }

        $maxFamilyNumber = Family::max('family_number') ?? 0;

        return view('santa.shopping-day', compact(
            'assignments', 'coordinators', 'allCategories',
            'assignedCategories', 'assignedRanges', 'maxFamilyNumber'
        ));
    }

    public function createAssignment(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'split_type' => ['required', 'in:category,family_range'],
            'categories' => ['required_if:split_type,category', 'nullable', 'array'],
            'categories.*' => ['string'],
            'family_start' => ['required_if:split_type,family_range', 'nullable', 'integer', 'min:1'],
            'family_end' => ['required_if:split_type,family_range', 'nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        ShoppingAssignment::create($request->only([
            'user_id', 'split_type', 'categories', 'family_start', 'family_end', 'notes',
        ]));

        return redirect()->route('santa.shoppingDay')
            ->with('success', 'Shopping assignment created.');
    }

    public function deleteAssignment(ShoppingAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('santa.shoppingDay')
            ->with('success', 'Shopping assignment removed.');
    }

    public function users(): View
    {
        return view('santa.users', [
            'users' => User::orderBy('first_name')->get(),
        ]);
    }

    /**
     * Create a new user account (Santa-initiated registration).
     * Replaces legacy Santa/InsertNewUser.php
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $roleToPermission = [
            'family' => 7,
            'coordinator' => 8,
            'santa' => 9,
        ];

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => $request->password,
            'permission' => $roleToPermission[$request->role],
        ]);

        // Assign Spatie role if package is installed
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($request->role);
        }

        return redirect()->route('santa.users')
            ->with('success', "User '{$user->username}' created successfully.")
            ->with('created_credentials', "Username: {$user->username}  |  Password: {$request->password}");
    }

    /**
     * Update an existing user account.
     * Replaces legacy Santa/UpdateUser.php
     */
    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $roleToPermission = [
            'family' => 7,
            'coordinator' => 8,
            'santa' => 9,
            'inactive' => 0,
        ];

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'permission' => $roleToPermission[$request->role],
        ];

        // Only update password if one was provided
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Sync Spatie role if package is installed
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([]);
            if ($request->role !== 'inactive') {
                $user->assignRole($request->role);
            }
        }

        return redirect()->route('santa.users')
            ->with('success', "User '{$user->username}' updated successfully.");
    }

    /**
     * Reset a user's password (admin-initiated).
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $request->password,
        ]);

        return redirect()->route('santa.users')
            ->with('success', "Password reset for '{$user->username}'.");
    }
}
