<?php

namespace App\Http\Controllers;

use App\Enums\PackingStatus;
use App\Helpers\QrCodeHelper;
use App\Models\Family;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Services\PackingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackingController extends Controller
{
    public function __construct(
        private readonly PackingService $packingService
    ) {}

    public function dashboard(): View
    {
        return view('santa.packing.dashboard');
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->get('status');

        $query = PackingList::with(['family', 'volunteer', 'items']);

        if ($statusFilter === 'unfulfilled') {
            $query->whereHas('items', fn ($q) => $q->where('status', \App\Enums\PackingItemStatus::Unfulfilled));
        } elseif ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('family', function ($q) use ($search) {
                $q->where('family_name', 'LIKE', "%{$search}%")
                    ->orWhere('family_number', 'LIKE', "%{$search}%");
            });
        }

        $packingLists = $query->orderBy('created_at', 'desc')->paginate(25);

        // Summary counts
        $counts = [
            'all' => PackingList::count(),
            'pending' => PackingList::where('status', PackingStatus::Pending)->count(),
            'in_progress' => PackingList::where('status', PackingStatus::InProgress)->count(),
            'complete' => PackingList::where('status', PackingStatus::Complete)->count(),
            'verified' => PackingList::where('status', PackingStatus::Verified)->count(),
            'unfulfilled' => PackingList::whereHas('items', fn ($q) => $q->where('status', \App\Enums\PackingItemStatus::Unfulfilled))->count(),
        ];

        return view('santa.packing.index', compact('packingLists', 'counts', 'statusFilter'));
    }

    public function show(PackingList $packingList): View
    {
        $packingList->load(['family.children', 'items.category', 'items.child', 'items.packer', 'volunteer', 'verifier']);

        $progress = $packingList->progressSummary();

        // Group items by type
        $foodItems = $packingList->items->filter(fn ($item) => $item->child_id === null && $item->category?->type !== 'baby');
        $giftItems = $packingList->items->filter(fn ($item) => $item->child_id !== null);
        $babyItems = $packingList->items->filter(fn ($item) => $item->child_id === null && $item->category?->type === 'baby');

        return view('santa.packing.show', compact('packingList', 'progress', 'foodItems', 'giftItems', 'babyItems'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $statusFilter = $request->input('status_filter', 'all');
        $seasonYear = Setting::get('season_year', date('Y'));
        $count = $this->packingService->generateAllPackingLists($seasonYear, $statusFilter !== 'all' ? $statusFilter : null);

        $redirect = redirect()->route('packing.index');
        if ($statusFilter && $statusFilter !== 'all') {
            $redirect = redirect()->route('packing.index', ['status' => $statusFilter]);
        }

        return $redirect->with('success', "Generated packing lists for {$count} families.");
    }

    public function generateSingle(Family $family): RedirectResponse
    {
        $list = $this->packingService->generatePackingList($family);

        return redirect()->route('packing.show', $list)
            ->with('success', "Packing list generated for {$family->family_name}.");
    }

    public function refreshList(PackingList $packingList): RedirectResponse
    {
        $this->packingService->refreshPackingList($packingList);

        return redirect()->route('packing.show', $packingList)
            ->with('success', 'Packing list refreshed.');
    }

    public function print(Request $request, PackingList $packingList): View
    {
        $packingList->load(['family', 'items.category', 'items.child']);

        $printType = $request->get('type', 'both');
        if (! in_array($printType, ['food', 'gift', 'both'])) {
            $printType = 'both';
        }

        $qrCodeUrl = url("/warehouse/mobile-scan?token={$packingList->qr_token}");
        $qrCode = QrCodeHelper::generateBase64($qrCodeUrl, 4);

        // Group items by type; apply filter based on $printType
        $allFoodItems = $packingList->items->filter(fn ($item) => $item->child_id === null && $item->category?->type !== 'baby')->sortBy('sort_order');
        $allGiftItems = $packingList->items->filter(fn ($item) => $item->child_id !== null)->sortBy('sort_order');
        $allBabyItems = $packingList->items->filter(fn ($item) => $item->child_id === null && $item->category?->type === 'baby')->sortBy('sort_order');

        $foodItems = in_array($printType, ['food', 'both']) ? $allFoodItems : collect();
        $giftItems = in_array($printType, ['gift', 'both']) ? $allGiftItems : collect();
        $babyItems = in_array($printType, ['gift', 'both']) ? $allBabyItems : collect();

        return view('santa.packing.print', compact('packingList', 'qrCode', 'foodItems', 'giftItems', 'babyItems', 'printType'));
    }

    public function printBatch(Request $request): View
    {
        $request->validate([
            'list_ids' => 'required|array',
            'list_ids.*' => 'exists:packing_lists,id',
        ]);

        $printType = $request->input('type', 'both');
        if (! in_array($printType, ['food', 'gift', 'both'])) {
            $printType = 'both';
        }

        $lists = PackingList::with(['family', 'items.category', 'items.child'])
            ->whereIn('id', $request->list_ids)
            ->get();

        $printData = $lists->map(function ($list) use ($printType) {
            $qrCodeUrl = url("/warehouse/mobile-scan?token={$list->qr_token}");

            $allFoodItems = $list->items->filter(fn ($item) => $item->child_id === null && $item->category?->type !== 'baby')->sortBy('sort_order');
            $allGiftItems = $list->items->filter(fn ($item) => $item->child_id !== null)->sortBy('sort_order');
            $allBabyItems = $list->items->filter(fn ($item) => $item->child_id === null && $item->category?->type === 'baby')->sortBy('sort_order');

            return [
                'list' => $list,
                'qrCode' => QrCodeHelper::generateBase64($qrCodeUrl, 4),
                'foodItems' => in_array($printType, ['food', 'both']) ? $allFoodItems : collect(),
                'giftItems' => in_array($printType, ['gift', 'both']) ? $allGiftItems : collect(),
                'babyItems' => in_array($printType, ['gift', 'both']) ? $allBabyItems : collect(),
            ];
        });

        return view('santa.packing.print-batch', compact('printData', 'printType'));
    }

    public function markItemPacked(PackingList $packingList, PackingItem $packingItem): RedirectResponse
    {
        if ($packingItem->packing_list_id !== $packingList->id) {
            abort(404);
        }

        $result = $this->packingService->markItemPacked($packingItem, auth()->user());

        return redirect()->route('packing.show', $packingList)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function verify(PackingList $packingList): RedirectResponse
    {
        $success = $this->packingService->verifyPackingList($packingList, auth()->user());

        if ($success) {
            return redirect()->route('packing.show', $packingList)
                ->with('success', 'Packing list verified successfully.');
        }

        return redirect()->route('packing.show', $packingList)
            ->with('error', 'Cannot verify — not all items are packed.');
    }

    public function summary(Request $request): View
    {
        try {
            $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        } catch (\Exception) {
            $date = Carbon::today();
        }
        $summary = $this->packingService->getEndOfDaySummary($date);

        return view('santa.packing.summary', compact('summary', 'date'));
    }

    public function updateNotes(PackingList $packingList, Request $request): RedirectResponse
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        $packingList->update(['notes' => $request->notes]);

        return redirect()->route('packing.show', $packingList)
            ->with('success', 'Notes updated.');
    }
}
