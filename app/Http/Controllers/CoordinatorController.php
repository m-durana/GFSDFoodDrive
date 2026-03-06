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

        return view('coordinator.index', compact('stats', 'schoolRanges'));
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

        $viewData = compact('families');

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
