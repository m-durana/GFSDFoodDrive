<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Helpers\QrCodeHelper;
use App\Jobs\GeneratePdfJob;
use App\Models\Child;
use App\Models\Family;
use App\Models\SchoolRange;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CoordinatorController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_families' => Family::count(),
            'assigned_families' => Family::whereNotNull('family_number')->count(),
            'total_children' => Child::count(),
            'children_no_gifts' => Child::where('gift_level', GiftLevel::None->value)->count(),
            'children_complete' => Child::where('gift_level', GiftLevel::Full->value)->count(),
            'unmerged_tags' => Child::where('mail_merged', false)->count(),
            'families_done' => Family::where('family_done', true)->count(),
        ];

        $schoolRanges = SchoolRange::orderBy('sort_order')->get();
        $sectionKpis = $this->sectionKpis(auth()->user());

        return view('coordinator.index', compact('stats', 'schoolRanges', 'sectionKpis'));
    }

    /**
     * REL-10: per-section KPI tiles for the coordinator dashboard. Only the
     * sections the user has access to are returned; Santa + System Coordinator
     * see all sections. Each KPI is one or two cheap aggregate queries — this
     * runs on the dashboard, not the high-traffic command center.
     */
    private function sectionKpis(?\App\Models\User $user): array
    {
        if (!$user) return [];

        $allSections = array_keys(\App\Support\CoordinatorSections::SECTIONS);
        $allowed = $user->isSanta() ? $allSections : $user->coordinatorSections();

        $kpis = [];
        foreach ($allowed as $slug) {
            $kpis[$slug] = match ($slug) {
                'giving-tree' => $this->kpisGivingTree(),
                'food'        => $this->kpisFood(),
                'packing'     => $this->kpisPacking(),
                'delivery'    => $this->kpisDelivery(),
                'business'    => $this->kpisBusiness(),
                'system'      => $this->kpisSystem(),
                default       => null,
            };
        }
        return array_filter($kpis);
    }

    private function kpisGivingTree(): array
    {
        $totalChildren = Child::count() ?: 1;
        $adopted = Child::whereNotNull('adoption_token')->count();
        $droppedOff = Child::where('gift_dropped_off', true)->count();
        return [
            'label' => 'Giving Tree',
            'tiles' => [
                ['v' => round(($adopted / $totalChildren) * 100) . '%', 'l' => 'Adopted', 'sub' => "{$adopted} of {$totalChildren} tags"],
                ['v' => Child::where('mail_merged', false)->count(), 'l' => 'Unprinted Tags'],
                ['v' => $droppedOff, 'l' => 'Gifts Dropped Off'],
            ],
        ];
    }

    private function kpisFood(): array
    {
        $assignments = \App\Models\ShoppingAssignment::with('checks')->get();
        $checked = 0; $total = 0;
        foreach ($assignments as $a) { $total += $a->getTotalItems(); $checked += $a->checks->count(); }
        $pct = $total > 0 ? round(($checked / $total) * 100) : 0;
        return [
            'label' => 'Food / Shopping',
            'tiles' => [
                ['v' => "{$pct}%", 'l' => 'Shopping', 'sub' => "{$checked} / {$total} items"],
                ['v' => $assignments->count(), 'l' => 'Active Assignments'],
            ],
        ];
    }

    private function kpisPacking(): array
    {
        $pl = \App\Models\PackingList::all();
        $byStatus = $pl->groupBy(fn($p) => $p->status?->value ?? 'pending')->map->count();
        return [
            'label' => 'Packing',
            'tiles' => [
                ['v' => $byStatus['verified']    ?? 0, 'l' => 'Verified'],
                ['v' => $byStatus['complete']    ?? 0, 'l' => 'Complete'],
                ['v' => $byStatus['in_progress'] ?? 0, 'l' => 'In Progress'],
                ['v' => $byStatus['pending']     ?? 0, 'l' => 'Pending'],
            ],
        ];
    }

    private function kpisDelivery(): array
    {
        $delivered = Family::where('delivery_status', \App\Enums\DeliveryStatus::Delivered)->count();
        $inTransit = Family::where('delivery_status', \App\Enums\DeliveryStatus::InTransit)->count();
        $pending = Family::whereNotNull('family_number')
            ->where(fn($q) => $q->where('delivery_status', \App\Enums\DeliveryStatus::Pending)->orWhereNull('delivery_status'))
            ->count();
        return [
            'label' => 'Delivery',
            'tiles' => [
                ['v' => $delivered, 'l' => 'Delivered'],
                ['v' => $inTransit, 'l' => 'In Transit'],
                ['v' => $pending, 'l' => 'Pending'],
                ['v' => \App\Models\DeliveryRoute::count(), 'l' => 'Routes'],
            ],
        ];
    }

    private function kpisBusiness(): array
    {
        return [
            'label' => 'Business / Media',
            'tiles' => [
                ['v' => Family::count(), 'l' => 'Families'],
                ['v' => Family::sum('number_of_family_members'), 'l' => 'Total People'],
                ['v' => Family::where('severe_need', '!=', '')->whereNotNull('severe_need')->count(), 'l' => 'Severe Need'],
            ],
        ];
    }

    private function kpisSystem(): array
    {
        return [
            'label' => 'System',
            'tiles' => [
                ['v' => \App\Models\User::where('permission', '>=', 8)->count(), 'l' => 'Staff Accounts'],
                ['v' => \App\Models\AuditLog::where('created_at', '>=', now()->subDay())->count(), 'l' => 'Audit Events (24h)'],
            ],
        ];
    }

    public function giftTags(Request $request)
    {
        $query = Child::with('family')->whereHas('family', function ($q) {
            $q->whereNotNull('family_number');
        });

        $filter = $request->get('filter', 'unmerged');
        if ($filter === 'unmerged') {
            $query->where('mail_merged', false);
        }

        $query->whereNull('adoption_token');

        if ($request->filled('range_start') && $request->filled('range_end')) {
            $query->whereHas('family', function ($q) use ($request) {
                $q->whereBetween('family_number', [$request->range_start, $request->range_end]);
            });
        }

        $children = $query->get()->sortBy(fn($c) => $c->family->family_number);

        if ($request->boolean('mark_merged')) {
            Child::whereIn('id', $children->pluck('id'))->update(['mail_merged' => true]);
        }

        $adoptEnabled = Setting::get('adopt_a_tag_enabled', '0') === '1';
        $qrCodes = [];
        foreach ($children as $child) {
            $url = $adoptEnabled
                ? route('adopt.show', $child)
                : QrCodeHelper::scanUrl($child->id);
            $qrCodes[$child->id] = QrCodeHelper::generateBase64($url, 5);
        }

        $paperSize = Setting::get('paper_size', 'letter');
        $adoptDeadline = Setting::get('adopt_a_tag_deadline', '');

        if (empty($adoptDeadline)) {
            $deliveryDate = Setting::get('delivery_date', '');
            if ($deliveryDate) {
                try {
                    $adoptDeadline = \Carbon\Carbon::parse($deliveryDate)->subDays(9)->format('F j, Y');
                } catch (\Exception $e) {
                }
            }
        }

        $viewData = compact('children', 'filter', 'qrCodes', 'paperSize', 'adoptDeadline');

        if ($request->boolean('sync')) {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return response()->view('documents.gift-tags', $viewData);
            }
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.gift-tags', $viewData);
            $pdf->setPaper($paperSize);
            return $pdf->stream('gift-tags.pdf');
        }

        return $this->dispatchPdfJob('documents.gift-tags', $viewData, 'gift-tags.pdf', $paperSize);
    }

    public function familySummary(Request $request)
    {
        $query = Family::query();

        if ($request->filled('range_start') && $request->filled('range_end')) {
            $query->whereBetween('family_number', [$request->range_start, $request->range_end]);
        }

        $families = $query->whereNotNull('family_number')->with('children')->orderBy('family_number')->get();

        $paperSize = Setting::get('paper_size', 'letter');

        $viewData = compact('families');

        if ($request->boolean('sync')) {
            set_time_limit(120);
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return response()->view('documents.family-summary', $viewData);
            }
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.family-summary', $viewData);
            $pdf->setPaper($paperSize);
            return $pdf->stream('family-summary.pdf');
        }

        return $this->dispatchPdfJob('documents.family-summary', $viewData, 'family-summary.pdf', $paperSize);
    }

    public function deliveryDay(Request $request)
    {
        $query = Family::whereNotNull('family_number');

        if ($request->filled('delivery_date')) {
            try {
                $parsed = \Carbon\Carbon::parse($request->delivery_date);
                $query->whereDate('delivery_date', $parsed->toDateString());
            } catch (\Exception $e) {
                $query->where('delivery_date', $request->delivery_date);
            }
        }

        if ($request->filled('delivery_team')) {
            $query->where('delivery_team', $request->delivery_team);
        }

        $families = $query->with('children')->orderBy('family_number')->get();

        $paperSize = Setting::get('paper_size', 'letter');

        // Only Santa + System Coordinator should see real names, phones, addresses on the
        // delivery-day PDF. Coordinators downloading this PDF (which is handed to drivers)
        // get a family-number-only variant.
        $showPii = $request->user()?->canSeePii() ?? false;

        $viewData = compact('families', 'showPii');

        if ($request->boolean('sync')) {
            set_time_limit(120);
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return response()->view('documents.delivery-day', $viewData);
            }
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.delivery-day', $viewData);
            $pdf->setPaper($paperSize);
            return $pdf->stream('delivery-day.pdf');
        }

        return $this->dispatchPdfJob('documents.delivery-day', $viewData, 'delivery-day.pdf', $paperSize);
    }

    /**
     * Check the status of a background PDF generation job.
     */
    public function pdfStatus(string $jobKey): JsonResponse
    {
        $status = Cache::get("pdf:{$jobKey}", ['status' => 'unknown', 'message' => 'Job not found.']);

        return response()->json($status);
    }

    /**
     * Download a completed background PDF.
     */
    public function pdfDownload(string $jobKey)
    {
        $status = Cache::get("pdf:{$jobKey}");

        if (!$status || $status['status'] !== 'complete' || !isset($status['path'])) {
            abort(404, 'PDF not found or not ready yet.');
        }

        $path = $status['path'];
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'PDF file not found.');
        }

        return response()->download(
            Storage::disk('local')->path($path),
            $status['filename'] ?? 'document.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Dispatch a PDF generation job to run in the background.
     */
    private function dispatchPdfJob(string $view, array $data, string $filename, string $paperSize): JsonResponse
    {
        $jobKey = Str::random(16);

        Cache::put("pdf:{$jobKey}", ['status' => 'queued', 'message' => 'PDF generation queued...'], 600);

        GeneratePdfJob::dispatch($jobKey, $view, $data, $filename, $paperSize);

        return response()->json([
            'job_key' => $jobKey,
            'status_url' => route('coordinator.pdfStatus', $jobKey),
            'download_url' => route('coordinator.pdfDownload', $jobKey),
        ]);
    }
}
