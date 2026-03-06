<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags — GFSD Food Drive</title>
    <style>
        @page {
            size: {{ $paperSize ?? 'letter' }};
            /* Safe margins. The table height handles the rest automatically. */
            margin: 0.25in;
        }

        /* Essential to tell the PDF engine what "100%" means */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Century Gothic', 'Trebuchet MS', Arial, sans-serif;
            color: #000;
        }

        * {
            box-sizing: border-box;
        }

        .page-break {
            page-break-before: always;
        }

        /* Container exactly matches the printable area between margins */
        .page-container {
            width: 100%;
            height: 100%;
            page-break-inside: avoid;
        }

        .page-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* --- THE SPLIT ROW ARCHITECTURE --- */
        /* Forces equal spacing across the whole page */
        .content-row { height: 18%; }
        .footer-row { height: 2%; }

        /* The top half of the dashed box */
        .content-cell {
            width: 50%;
            vertical-align: top;
            padding: 0.08in 0.1in 0 0.1in;
            border-top: 1px dashed #bbb;
            border-left: 1px dashed #bbb;
            border-right: 1px dashed #bbb;
            border-bottom: none; /* Flow seamlessly into the footer cell */
            overflow: hidden;
        }

        /* The bottom half of the dashed box */
        .footer-cell {
            width: 50%;
            vertical-align: bottom;
            padding: 0 0.1in 0.06in 0.1in;
            border-bottom: 1px dashed #bbb;
            border-left: 1px dashed #bbb;
            border-right: 1px dashed #bbb;
            border-top: none; /* Flow seamlessly from the content cell */
        }

        .empty-cell {
            border: none !important;
        }

        /* --- TYPOGRAPHY & INNER LAYOUT --- */
        .card-header {
            margin-bottom: 1pt;
            /* Removed the weird line! */
        }

        .card-number {
            font-size: 20pt;
            font-weight: bold;
            color: #000;
            line-height: 1;
            display: inline-block;
        }

        .card-qr {
            float: right;
        }

        .card-qr img {
            width: 1in;
            height: 1in;
        }

        .card-fields {
            margin-top: 2pt;
        }

        .card-fields .card-field { margin-bottom: 2pt; }
        .card-fields .label { font-weight: bold; }

        /* Restored original sizing logic */
        .size-xlarge { font-size: 11pt; line-height: 1.35; }
        .size-large  { font-size: 10pt; line-height: 1.3;  }
        .size-medium { font-size: 9pt;  line-height: 1.25; }
        .size-small  { font-size: 8pt;  line-height: 1.2;  }
        .size-micro  { font-size: 7pt;  line-height: 1.15; }

        /* Restored exactly to your original design (Bold + Solid Top Line) */
        .card-footer {
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
            padding-top: 3pt;
            border-top: 1px solid #999;
            color: #333;
            line-height: 1.2;
        }

        .card-footer .email {
            font-weight: normal;
            font-style: italic;
            font-size: 6pt;
            display: block;
            margin-top: 1pt;
        }

        .card-footer .deadline {
            font-weight: normal;
            font-size: 6pt;
            color: #c00;
            display: block;
            margin-top: 1pt;
        }
    </style>
</head>
<body>

@if($children->count() === 0)
<p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No children match the selected filter.</p>
@else
@foreach($children->chunk(10) as $pageIndex => $page)
@if($pageIndex > 0)
<div class="page-break"></div>
@endif

<div class="page-container">
    <table class="page-table">
        @php
        $slots = $page->values()->all();
        while (count($slots) < 10) { $slots[] = null; }
        $rows = array_chunk($slots, 2);
        @endphp

        @foreach($rows as $pair)
        <!-- ROW 1: TAG CONTENT -->
        <tr class="content-row">
            @foreach($pair as $child)
            @if($child)
            @php
            $allText = $child->gender . $child->age . $child->clothing_options . $child->clothing_styles . $child->all_sizes . $child->toy_ideas . $child->gift_preferences;
            $charCount = strlen($allText);

            $sizeClass = 'size-xlarge';
            if ($charCount > 220) { $sizeClass = 'size-micro'; }
            elseif ($charCount > 160) { $sizeClass = 'size-small'; }
            elseif ($charCount > 100) { $sizeClass = 'size-medium'; }
            elseif ($charCount > 50) { $sizeClass = 'size-large'; }
            @endphp
            <td class="content-cell">
                <div class="card-header">
                    @if(isset($qrCodes[$child->id]))
                    <div class="card-qr">
                        <img src="{{ $qrCodes[$child->id] }}" alt="QR">
                    </div>
                    @endif
                    <div class="card-number">#{{ $child->family->family_number }}</div>
                </div>

                <div class="card-fields {{ $sizeClass }}">
                    <div class="card-field"><span class="label">Gender:</span> {{ $child->gender }}</div>
                    <div class="card-field"><span class="label">Age:</span> {{ $child->age }}</div>
                    @if($child->clothing_options)
                    <div class="card-field"><span class="label">Clothing:</span> {{ $child->clothing_options }}</div>
                    @endif
                    @if($child->clothing_styles)
                    <div class="card-field"><span class="label">Styles:</span> {{ $child->clothing_styles }}</div>
                    @endif
                    @if($child->all_sizes)
                    <div class="card-field"><span class="label">Sizes:</span> {{ $child->all_sizes }}</div>
                    @endif
                    @if($child->toy_ideas)
                    <div class="card-field"><span class="label">Toy Ideas:</span> {{ $child->toy_ideas }}</div>
                    @endif
                    @if($child->gift_preferences)
                    <div class="card-field"><span class="label">Gift Pref:</span> {{ $child->gift_preferences }}</div>
                    @endif
                </div>
            </td>
            @else
            <td class="content-cell empty-cell"></td>
            @endif
            @endforeach
        </tr>

        <!-- ROW 2: TAG FOOTER -->
        <tr class="footer-row">
            @foreach($pair as $child)
            @if($child)
            <td class="footer-cell">
                @php
                    $tagDeadline = $adoptDeadline ?? '';
                    $tagEmail = \App\Models\Setting::get('primary_contact_email', 'fooddrive@gfalls.wednet.edu');
                    $tagPhone = \App\Models\Setting::get('primary_phone');
                @endphp
                <div class="card-footer">
                    Please bring in all gifts <u>UNWRAPPED</u> with this tag attached{{ $tagDeadline ? " by {$tagDeadline}" : '' }}.
                    <span class="email">Questions? Email us at: {{ $tagEmail }}{{ $tagPhone ? " or contact us at: {$tagPhone}" : '' }}</span>
                </div>
            </td>
            @else
            <td class="footer-cell empty-cell"></td>
            @endif
            @endforeach
        </tr>
        @endforeach
    </table>
</div>
@endforeach
@endif

</body>
</html>