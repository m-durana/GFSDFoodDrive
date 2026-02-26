<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Day Sheets — GFSD Food Drive</title>
    <style>
        /* Matches original 709.docx - Maiandra GD, checkboxes, driver fields */
        @page {
            size: letter;
            margin: 0.5in 0.6in;
        }

        body {
            font-family: 'Maiandra GD', 'Gill Sans', 'Trebuchet MS', sans-serif;
            font-size: 16pt;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .delivery-page {
            page-break-after: always;
        }

        .delivery-page:last-child {
            page-break-after: auto;
        }

        /* Top section: Pickup/Delivery checkboxes */
        .toggle-row {
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 12pt;
            padding-bottom: 8pt;
            border-bottom: 2pt solid #000;
        }

        .checkbox {
            display: inline-block;
            width: 18pt;
            height: 18pt;
            border: 2pt solid #000;
            vertical-align: middle;
            margin: 0 4pt;
            text-align: center;
            line-height: 16pt;
            font-size: 14pt;
        }

        .checkbox.checked {
            background: #000;
            color: #fff;
        }

        /* Logistics fields */
        .logistics-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10pt;
        }

        .logistics-grid td {
            padding: 4pt 6pt;
            font-size: 15pt;
            vertical-align: bottom;
        }

        .logistics-grid .label {
            font-weight: bold;
            width: 35%;
        }

        .logistics-grid .line {
            border-bottom: 1.5pt solid #000;
        }

        /* Section divider */
        .section-divider {
            border: none;
            border-top: 2.5pt solid #000;
            margin: 10pt 0;
        }

        /* Family info section */
        .info-row {
            margin: 6pt 0;
            font-size: 16pt;
            line-height: 1.6;
        }

        .info-row .label {
            font-weight: bold;
            font-size: 16pt;
        }

        .info-row .value {
            font-size: 16pt;
            border-bottom: 1pt solid #666;
            padding-bottom: 1pt;
        }

        .info-row .value.empty {
            display: inline-block;
            min-width: 250pt;
            border-bottom: 1pt solid #999;
        }

        /* Warning boxes */
        .warning-box {
            border: 2pt solid #c00;
            background: #fff5f5;
            padding: 6pt 10pt;
            margin: 8pt 0;
            font-size: 14pt;
            font-weight: bold;
        }

        /* Bottom checkoff section */
        .checkoff {
            margin-top: 12pt;
            padding-top: 8pt;
            border-top: 1.5pt solid #000;
            font-size: 14pt;
        }

        .checkoff-item {
            display: inline-block;
            margin-right: 20pt;
        }

        .notes-line {
            display: block;
            margin-top: 8pt;
            font-size: 14pt;
        }

        .notes-line .underline {
            display: inline-block;
            width: 80%;
            border-bottom: 1pt solid #000;
        }
    </style>
</head>
<body>
    @forelse($families as $family)
        <div class="delivery-page">
            <!-- Pickup / Delivery toggle -->
            <div class="toggle-row">
                @if($family->delivery_preference === 'Pickup')
                    <span class="checkbox checked">X</span> PICKUP
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox"></span> DELIVERY
                @elseif($family->delivery_preference === 'Delivery')
                    <span class="checkbox"></span> PICKUP
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox checked">X</span> DELIVERY
                @else
                    <span class="checkbox"></span> PICKUP
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox"></span> DELIVERY
                @endif
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-size: 28pt; font-weight: bold;">#{{ $family->family_number }}</span>
            </div>

            <!-- Logistics -->
            <table class="logistics-grid">
                <tr>
                    <td class="label">Scheduled Time:</td>
                    <td class="line">{{ $family->delivery_date }} {{ $family->delivery_time }}</td>
                </tr>
                <tr>
                    <td class="label">Driver:</td>
                    <td class="line">{{ $family->delivery_team ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Departure Time:</td>
                    <td class="line">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Return Time:</td>
                    <td class="line">&nbsp;</td>
                </tr>
            </table>

            <hr class="section-divider">

            <!-- Family Information -->
            <div class="info-row">
                <span class="label">Name(s):</span>
                <span class="value">{{ $family->family_name }}</span>
            </div>

            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value">{{ $family->phone1 }}</span>
            </div>

            @if($family->phone2)
                <div class="info-row">
                    <span class="label">Alt Phone:</span>
                    <span class="value">{{ $family->phone2 }}</span>
                </div>
            @endif

            <div class="info-row">
                <span class="label">Additional Phone:</span>
                <span class="value empty">&nbsp;</span>
            </div>

            <div class="info-row">
                <span class="label">Physical Address:</span>
                <span class="value">{{ $family->address }}</span>
            </div>

            <div class="info-row">
                <span class="label">New Address:</span>
                <span class="value empty">&nbsp;</span>
            </div>

            <div class="info-row">
                <span class="label">Landmarks / Directions:</span>
                <span class="value empty">&nbsp;</span>
            </div>

            @if($family->delivery_reason)
                <div class="warning-box">
                    Cannot deliver because: {{ $family->delivery_reason }}
                </div>
            @endif

            <div class="info-row">
                <span class="label">Special Instructions:</span>
                <span class="value empty">&nbsp;</span>
            </div>

            <div class="info-row">
                <span class="label">Pets:</span>
                <span class="value">{{ $family->pet_information ? $family->pet_information . ' (pet food included)' : 'None noted' }}</span>
            </div>

            @if($family->preferred_language && $family->preferred_language !== 'English')
                <div class="warning-box" style="border-color: #07c; background: #f0f7ff;">
                    LANGUAGE: {{ $family->preferred_language }}
                </div>
            @endif

            <div class="info-row">
                <span class="label">Family Size:</span>
                <span class="value">{{ $family->number_of_family_members }} ({{ $family->number_of_adults }} adults, {{ $family->number_of_children }} children)</span>
            </div>

            <!-- Checkoff section -->
            <div class="checkoff">
                <span class="checkoff-item"><span class="checkbox"></span> Delivered</span>
                <span class="checkoff-item"><span class="checkbox"></span> Left at door</span>
                <span class="checkoff-item"><span class="checkbox"></span> No answer</span>
                <span class="checkoff-item"><span class="checkbox"></span> Picked up</span>

                <span class="notes-line">
                    <span class="label">Notes:</span> <span class="underline">&nbsp;</span>
                </span>
                <span class="notes-line" style="margin-top: 4pt;">
                    <span class="underline" style="width: 95%;">&nbsp;</span>
                </span>
            </div>

            <!-- Return instructions -->
            <div style="margin-top: 10pt; padding-top: 6pt; border-top: 1pt solid #999; font-size: 12pt; color: #444; text-align: center;">
                Return this form to {{ \App\Models\Setting::get('delivery_return_to', 'System Engineers') }}.
                @if(\App\Models\Setting::get('hs_phone_number'))
                    Problems? HS Phone: <strong>{{ \App\Models\Setting::get('hs_phone_number') }}</strong>
                @endif
            </div>
        </div>
    @empty
        <p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No families match the selected filter.</p>
    @endforelse
</body>
</html>
