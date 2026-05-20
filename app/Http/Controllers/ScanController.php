<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    /**
     * Display child info from a scanned QR code (signed URL, no auth required).
     * The `signed` middleware on the route handles signature validation.
     */
    public function show(Request $request, Child $child): View
    {
        $child->load('family');

        return view('scan.show', compact('child'));
    }

    /**
     * Update gift status from the scan page.
     */
    public function update(\App\Http\Requests\UpdateScanGiftStatusRequest $request, Child $child): RedirectResponse
    {
        $child->update([
            'gift_level' => $request->gift_level,
            'gifts_received' => $request->gifts_received,
            'adopter_name' => $request->adopter_name,
        ]);

        // Redirect back to the signed show URL
        $signedUrl = url()->signedRoute('scan.show', ['child' => $child->id]);

        return redirect($signedUrl)->with('success', 'Gift status updated!');
    }
}
