<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Enums\TransactionType;
use App\Models\Child;
use App\Models\GiftBankItem;
use App\Models\WarehouseCategory;
use App\Models\WarehouseTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftBankController extends Controller
{
    public function index(Request $request): View
    {
        $query = GiftBankItem::with(['assignedChild.family', 'receivedByUser']);

        if ($request->filled('status')) {
            if ($request->status === 'unassigned') {
                $query->unassigned();
            } elseif ($request->status === 'assigned') {
                $query->assigned();
            }
        }
        if ($request->filled('age_range')) {
            $query->where('age_range', $request->age_range);
        }
        if ($request->filled('gender')) {
            $query->where('gender_suitability', $request->gender);
        }
        if ($request->filled('gift_type')) {
            $query->where('gift_type', 'like', "%{$request->gift_type}%");
        }

        $items = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        $totals = [
            'total' => GiftBankItem::count(),
            'unassigned' => GiftBankItem::unassigned()->count(),
            'assigned' => GiftBankItem::assigned()->count(),
        ];

        $childrenForAssign = Child::with('family')
            ->whereHas('family', fn($q) => $q->whereNotNull('family_number'))
            ->get(['id', 'family_id', 'gender', 'age'])
            ->map(fn($c) => [
                'id' => $c->id,
                'family_number' => $c->family->family_number,
                'family_name' => $c->family->family_name,
                'gender' => $c->gender,
                'age' => $c->age,
            ]);

        return view('warehouse.gift-bank', compact('items', 'totals', 'childrenForAssign'));
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'age_range' => ['nullable', 'string', 'max:50'],
            'gender_suitability' => ['nullable', 'string', 'in:male,female,neutral'],
            'gift_type' => ['nullable', 'string', 'max:100'],
            'donor_name' => ['nullable', 'string', 'max:200'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['received_by'] = $request->user()->id;

        $item = GiftBankItem::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', 'Gift added to Gift Bank.');
    }

    public function assign(Request $request, GiftBankItem $item, Child $child): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $item->update([
            'assigned_child_id' => $child->id,
            'assigned_at' => now(),
        ]);

        // Create warehouse transaction for traceability
        $categoryName = $this->giftCategoryForChild($child);
        $category = WarehouseCategory::where('name', $categoryName)->first()
            ?? WarehouseCategory::where('name', 'Gift - Neutral')->first();

        if ($category) {
            WarehouseTransaction::create([
                'category_id' => $category->id,
                'family_id' => $child->family_id,
                'child_id' => $child->id,
                'transaction_type' => TransactionType::In,
                'quantity' => 1,
                'source' => 'Gift Bank',
                'scanned_by' => $request->user()->id,
                'notes' => "Gift Bank: {$item->description}",
            ]);
        }

        // Update child's gift level if needed
        $child->update([
            'gift_level' => max($child->gift_level?->value ?? 0, GiftLevel::Moderate->value),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Gift assigned to child #{$child->family->family_number}.");
    }

    public function unassign(Request $request, GiftBankItem $item): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $item->update([
            'assigned_child_id' => null,
            'assigned_at' => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Gift unassigned.');
    }

    public function destroy(GiftBankItem $item): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $item->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Gift removed from bank.');
    }

    public function suggestions(Child $child): JsonResponse
    {
        $age = (int) ($child->age ?? 0);
        $ageRange = match (true) {
            $age <= 5 => '0-5',
            $age <= 12 => '6-12',
            default => '13-17',
        };

        $gender = strtolower($child->gender ?? 'neutral');

        $items = GiftBankItem::unassigned()
            ->forAgeRange($ageRange)
            ->forGender($gender)
            ->limit(20)
            ->get(['id', 'description', 'age_range', 'gender_suitability', 'gift_type', 'donor_name']);

        return response()->json($items);
    }

    private function giftCategoryForChild(Child $child): string
    {
        $gender = strtolower($child->gender ?? '');
        $age = (int) ($child->age ?? 0);

        if ($gender === 'male') {
            if ($age < 6) return 'Gift - Boy Under 6';
            if ($age <= 12) return 'Gift - Boy 6-12';
            return 'Gift - Boy 13-17';
        } elseif ($gender === 'female') {
            if ($age < 6) return 'Gift - Girl Under 6';
            if ($age <= 12) return 'Gift - Girl 6-12';
            return 'Gift - Girl 13-17';
        }

        return 'Gift - Neutral';
    }
}
