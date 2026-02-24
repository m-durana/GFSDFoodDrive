<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Helpers\QrCodeHelper;
use App\Models\Child;
use App\Models\Family;
use App\Models\SchoolRange;
use App\Models\Setting;
use Illuminate\Http\Request;
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

        // Generate QR codes for each child
        $qrCodes = [];
        foreach ($children as $child) {
            $qrCodes[$child->id] = QrCodeHelper::generateBase64(
                QrCodeHelper::scanUrl($child->id),
                3
            );
        }

        $paperSize = Setting::get('paper_size', 'letter');

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.gift-tags', compact('children', 'filter', 'qrCodes', 'paperSize'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.gift-tags', compact('children', 'filter', 'qrCodes', 'paperSize'));
        $pdf->setPaper($paperSize);

        return $pdf->stream('gift-tags.pdf');
    }

    public function familySummary(Request $request)
    {
        $query = Family::query();

        if ($request->filled('range_start') && $request->filled('range_end')) {
            $query->whereBetween('family_number', [$request->range_start, $request->range_end]);
        }

        $families = $query->whereNotNull('family_number')->orderBy('family_number')->get();

        $paperSize = Setting::get('paper_size', 'letter');

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.family-summary', compact('families'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.family-summary', compact('families'));
        $pdf->setPaper($paperSize);

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

        $paperSize = Setting::get('paper_size', 'letter');

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('documents.delivery-day', compact('families'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.delivery-day', compact('families'));
        $pdf->setPaper($paperSize);

        return $pdf->stream('delivery-day.pdf');
    }
}
