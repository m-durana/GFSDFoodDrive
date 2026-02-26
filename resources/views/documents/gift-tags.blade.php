<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags — GFSD Food Drive</title>
    <style>
        @page {
            size: {{ $paperSize ?? 'letter' }};
            margin: 0;
        }

        body {
            font-family: 'Century Gothic', 'Trebuchet MS', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        * {
            box-sizing: border-box;
        }

        .page-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .page-table td.card {
            width: 50%;
            height: {{ ($paperSize ?? 'letter') === 'a4' ? '98mm' : '3.6in' }};
            vertical-align: top;
            border: 1px dashed #bbb;
            overflow: hidden;
            padding: 0;
        }

        .card-inner {
            width: 100%;
            height: {{ ($paperSize ?? 'letter') === 'a4' ? '98mm' : '3.6in' }};
            border-collapse: collapse;
        }

        .card-inner td {
            padding: 0 0.15in;
        }

        .card-inner td.card-body {
            vertical-align: top;
            padding-top: 0.15in;
            height: 2.8in;
        }

        .card-inner td.card-foot {
            vertical-align: bottom;
            height: 0.5in;
            padding-bottom: 0.15in;
        }

        .card-header {
            margin-bottom: 2pt;
        }

        .card-number {
            font-size: 28pt;
            font-weight: bold;
            color: #000;
            line-height: 1;
            display: inline-block;
        }

        .card-qr {
            float: right;
        }

        .card-qr img {
            width: 0.55in;
            height: 0.55in;
        }

        /* --- DYNAMIC FONT SIZES --- */
        .card-fields {
            margin-top: 5pt;
        }

        .card-fields .card-field {
            margin-bottom: 3pt;
        }

        .card-fields .label {
            font-weight: bold;
        }

        .size-large {
            font-size: 13pt;
            line-height: 1.4;
        }

        .size-medium {
            font-size: 11pt;
            line-height: 1.3;
        }

        .size-small {
            font-size: 9pt;
            line-height: 1.2;
        }

        .size-micro {
            font-size: 7.5pt;
            line-height: 1.1;
        }
        /* ------------------------- */

        .card-footer {
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            padding-top: 6pt;
            border-top: 1px solid #999;
            color: #333;
            line-height: 1.3;
        }

        .card-footer .email {
            font-weight: normal;
            font-style: italic;
            font-size: 8pt;
            display: block;
            margin-top: 3pt;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
@if($children->count() === 0)
<p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No children match the selected filter.</p>
@else
@foreach($children->chunk(6) as $pageIndex => $page)
@if($pageIndex > 0)
<div class="page-break"></div>
@endif
<table class="page-table">
    @php
    $slots = $page->values()->all();
    while (count($slots) < 6) { $slots[] = null; }
    $rows = array_chunk($slots, 2);
    @endphp
    @foreach($rows as $pair)
    <tr>
        @foreach($pair as $child)
        @if($child)
        @php
        // 1. Gather all the text that will be printed
        $allText = $child->gender . $child->age . $child->clothing_options . $child->clothing_styles . $child->all_sizes . $child->toy_ideas . $child->gift_preferences;

        // 2. Count the characters
        $charCount = strlen($allText);

        // 3. Assign a class based on character thresholds
        $sizeClass = 'size-large'; // Default
        if ($charCount > 300) {
        $sizeClass = 'size-micro';
        } elseif ($charCount > 200) {
        $sizeClass = 'size-small';
        } elseif ($charCount > 100) {
        $sizeClass = 'size-medium';
        }
        @endphp
        <td class="card">
            <table class="card-inner">
                <tr>
                    <td class="card-body">
                        <div class="card-header">
                            @if(isset($qrCodes[$child->id]))
                            <div class="card-qr">
                                <img src="{{ $qrCodes[$child->id] }}" alt="QR">
                            </div>
                            @endif
                            <div class="card-number">#{{ $child->family->family_number }}</div>
                        </div>

                        <div class="card-fields {{ $sizeClass }}">
                            <div class="card-field">
                                <span class="label">Gender:</span> {{ $child->gender }}
                            </div>
                            <div class="card-field">
                                <span class="label">Age:</span> {{ $child->age }}
                            </div>
                            @if($child->clothing_options)
                            <div class="card-field">
                                <span class="label">Clothing:</span> {{ $child->clothing_options }}
                            </div>
                            @endif
                            @if($child->clothing_styles)
                            <div class="card-field">
                                <span class="label">Styles:</span> {{ $child->clothing_styles }}
                            </div>
                            @endif
                            @if($child->all_sizes)
                            <div class="card-field">
                                <span class="label">Sizes:</span> {{ $child->all_sizes }}
                            </div>
                            @endif
                            @if($child->toy_ideas)
                            <div class="card-field">
                                <span class="label">Toy Ideas:</span> {{ $child->toy_ideas }}
                            </div>
                            @endif
                            @if($child->gift_preferences)
                            <div class="card-field">
                                <span class="label">Gift Preferences:</span> {{ $child->gift_preferences }}
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="card-foot">
                        <div class="card-footer">
                            Please bring in all gifts <u>UNWRAPPED</u> with this tag attached.<br>
                            <span class="email">Questions? Email: fooddrive@gfalls.wednet.edu</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        @else
        <td class="card" style="border-color: transparent;"></td>
        @endif
        @endforeach
    </tr>
    @endforeach
</table>
@endforeach
@endif
</body>
</html>