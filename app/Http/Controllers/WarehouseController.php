<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmGiftDropoffRequest;
use App\Http\Requests\StoreWarehouseReceiptRequest;
use App\Models\Child;
use App\Models\Setting;
use App\Models\WarehouseCategory;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $warehouse
    ) {}

    public function index(Request $request)
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        // AJAX feed poll
        if ($request->ajax() || $request->has('_feed')) {
            $transactions = $this->warehouse->recentTransactions(20);
            return view('warehouse._feed', compact('transactions'));
        }

        $deficits = $this->warehouse->categoryDeficits($seasonYear);
        $giftProgress = $this->warehouse->giftProgressByAge($seasonYear);
        $sourceBreakdown = $this->warehouse->sourceBreakdown($seasonYear);
        $recentTransactions = $this->warehouse->recentTransactions(20);

        return view('warehouse.index', compact('deficits', 'giftProgress', 'sourceBreakdown', 'recentTransactions'));
    }

    public function receive()
    {
        return redirect()->route('warehouse.kiosk');
    }

    public function store(StoreWarehouseReceiptRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['ip_address'] = $request->ip();
        $transaction = $this->warehouse->recordReceipt($data, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('category'),
            ]);
        }

        return back()->with('success', 'Item received successfully.');
    }

    public function inventory(): View
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));
        $deficits = $this->warehouse->categoryDeficits($seasonYear);
        $categories = WarehouseCategory::active()->with('items')->orderBy('sort_order')->get();

        return view('warehouse.inventory', compact('deficits', 'categories'));
    }

    public function lookupBarcode(string $barcode): JsonResponse
    {
        $item = $this->warehouse->lookupBarcode($barcode);

        if ($item) {
            return response()->json([
                'found' => true,
                'item' => $item->load('category'),
            ]);
        }

        // Try external UPC database
        $external = $this->warehouse->lookupBarcodeExternal($barcode);
        if (is_array($external) && ($external['error'] ?? false)) {
            return response()->json([
                'found' => false,
                'error' => $external['message'] ?? 'External lookup failed.',
            ]);
        }
        if ($external) {
            return response()->json([
                'found' => false,
                'external' => $external,
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function giftDropoff(Child $child): View
    {
        $child->load('family');

        return view('warehouse.gift-dropoff', compact('child'));
    }

    public function confirmGiftDropoff(ConfirmGiftDropoffRequest $request, Child $child): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $transaction = $this->warehouse->confirmGiftDropoff($child, $request->user(), $request->input('gifts_received'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'child' => $child->fresh()->load('family'),
                'transaction' => $transaction,
            ]);
        }

        return redirect()->route('warehouse.index')->with('success', "Gift drop-off confirmed for {$child->family->family_name}.");
    }

    public function transactions(Request $request): View
    {
        $query = WarehouseTransaction::with(['category', 'item', 'scanner', 'family']);

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('barcode_scanned', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(50)->withQueryString();
        $categories = WarehouseCategory::active()->orderBy('sort_order')->get();

        return view('warehouse.transactions', compact('transactions', 'categories'));
    }

    public function kiosk(): View
    {
        $categories = WarehouseCategory::active()->orderBy('sort_order')->get();

        return view('warehouse.kiosk', compact('categories'));
    }

    public function mobileScan(): View
    {
        return view('warehouse.mobile-scan');
    }

    public function giftsIntake(): View
    {
        $children = Child::with('family')
            ->whereHas('family', fn($q) => $q->whereNotNull('family_number'))
            ->orderByRaw("CASE WHEN gift_dropped_off = 1 THEN 1 ELSE 0 END")
            ->orderBy('family_id')
            ->get();

        return view('warehouse.gifts-intake', compact('children'));
    }
}
