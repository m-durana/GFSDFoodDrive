<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Helpers\QrCodeHelper;
use App\Jobs\GeneratePdfBatch;
use App\Models\Child;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CoordinatorController extends Controller
{
    private const BATCH_SIZE = 50;

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

        return view('coordinator.index', compact('stats'));
    }

    public function giftTags(Request $request)
    {
        $query = Child::with('family')->whereHas('family', function ($q) {
            $q->whereNotNull('family_number');
        });

        // Filter: unmerged only (default), all, or by family number range
        $filter = $request->get('filter', 'unmerged');
        if ($filter === 'unmerged') {
            $query->where('mail_merged', false);
        }

        if ($request->filled('range_start') && $request->filled('range_end')) {
            $query->whereHas('family', function ($q) use ($request) {
                $q->whereBetween('family_number', [$request->range_start, $request->range_end]);
            });
        }

        $children = $query->get()->sortBy(fn($c) => $c->family->family_number);

        // Mark as merged if requested
        if ($request->boolean('mark_merged')) {
            Child::whereIn('id', $children->pluck('id'))->update(['mail_merged' => true]);
        }

        // Batch if > 50 items
        if ($children->count() > self::BATCH_SIZE && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return $this->dispatchBatches('gift-tags', $children->pluck('id')->toArray());
        }

        // Generate QR codes for each child
        $qrCodes = [];
        foreach ($children as $child) {
            $qrCodes[$child->id] = QrCodeHelper::generateBase64(
                QrCodeHelper::scanUrl($child->id),
                3
            );
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.gift-tags', compact('children', 'filter', 'qrCodes'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.gift-tags', compact('children', 'filter', 'qrCodes'));
        $pdf->setPaper('letter');

        return $pdf->stream('gift-tags.pdf');
    }

    public function familySummary(Request $request)
    {
        $query = Family::query();

        if ($request->filled('range_start') && $request->filled('range_end')) {
            $query->whereBetween('family_number', [$request->range_start, $request->range_end]);
        }

        $families = $query->whereNotNull('family_number')->orderBy('family_number')->get();

        // Batch if > 50 items
        if ($families->count() > self::BATCH_SIZE && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return $this->dispatchBatches('family-summary', $families->pluck('id')->toArray());
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.family-summary', compact('families'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.family-summary', compact('families'));
        $pdf->setPaper('letter');

        return $pdf->stream('family-summary.pdf');
    }

    public function deliveryDay(Request $request)
    {
        $query = Family::whereNotNull('family_number');

        if ($request->filled('delivery_date')) {
            $query->where('delivery_date', $request->delivery_date);
        }

        if ($request->filled('delivery_team')) {
            $query->where('delivery_team', $request->delivery_team);
        }

        $families = $query->orderBy('family_number')->get();

        // Batch if > 50 items
        if ($families->count() > self::BATCH_SIZE && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return $this->dispatchBatches('delivery-day', $families->pluck('id')->toArray());
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.delivery-day', compact('families'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.delivery-day', compact('families'));
        $pdf->setPaper('letter');

        return $pdf->stream('delivery-day.pdf');
    }

    public function pdfStatus(string $batchId)
    {
        $statusFile = "pdf-batches/{$batchId}/status.json";

        if (!Storage::exists($statusFile)) {
            abort(404, 'Batch not found.');
        }

        $status = json_decode(Storage::get($statusFile), true);

        return view('coordinator.pdf-status', compact('status', 'batchId'));
    }

    public function pdfDownload(string $batchId, int $batchNumber)
    {
        $file = "pdf-batches/{$batchId}/batch-{$batchNumber}.pdf";

        if (!Storage::exists($file)) {
            abort(404, 'PDF not ready yet.');
        }

        $type = json_decode(Storage::get("pdf-batches/{$batchId}/status.json"), true)['type'] ?? 'document';

        return Storage::download($file, "{$type}-batch-{$batchNumber}.pdf");
    }

    private function dispatchBatches(string $type, array $itemIds)
    {
        $batchId = Str::uuid()->toString();
        $chunks = array_chunk($itemIds, self::BATCH_SIZE);
        $totalBatches = count($chunks);

        // Initialize status file
        $status = [
            'type' => $type,
            'total' => count($itemIds),
            'total_batches' => $totalBatches,
            'completed' => 0,
            'batches' => [],
        ];

        foreach ($chunks as $index => $chunk) {
            $batchNumber = $index + 1;
            $status['batches'][$batchNumber] = ['status' => 'pending', 'file' => null];
        }

        Storage::makeDirectory("pdf-batches/{$batchId}");
        Storage::put("pdf-batches/{$batchId}/status.json", json_encode($status));

        // Dispatch jobs
        foreach ($chunks as $index => $chunk) {
            GeneratePdfBatch::dispatch($batchId, $index + 1, $type, $chunk);
        }

        return redirect()->route('coordinator.pdfStatus', $batchId);
    }
}
