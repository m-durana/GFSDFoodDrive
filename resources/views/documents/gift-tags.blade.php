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

        .page-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Letter = 11in tall, 3 rows → 3.667in each. A4 = 11.69in → 3.897in each. */
        .page-table td.card {
            width: 50%;
            height: {{ ($paperSize ?? 'letter') === 'a4' ? '3.89in' : '3.66in' }};
            vertical-align: top;
            padding: 0.12in 0.18in;
            border: 1px dashed #bbb;
            overflow: hidden;
            position: relative;
        }

        .card-header {
            position: relative;
            height: 0.7in;
            margin-bottom: 2pt;
        }

        .card-number {
            font-size: 28pt;
            font-weight: bold;
            color: #000;
            line-height: 1;
            padding-top: 0.05in;
        }

        .card-qr {
            position: absolute;
            top: 0;
            right: 0;
            width: 0.65in;
            height: 0.65in;
        }

        .card-qr img {
            width: 0.65in;
            height: 0.65in;
        }

        /* Default font size — fields with lots of content get smaller class */
        .card-fields {
            font-size: 10pt;
            line-height: 1.35;
        }

        .card-fields.compact {
            font-size: 8pt;
            line-height: 1.25;
        }

        .card-field {
            margin-bottom: 1pt;
        }

        .card-field .label {
            font-weight: bold;
        }

        .card-footer {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            margin-top: 4pt;
            padding-top: 3pt;
            border-top: 1px solid #999;
            color: #333;
            line-height: 1.3;
        }

        .card-footer .email {
            font-weight: normal;
            font-style: italic;
            font-size: 6pt;
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
                @foreach($page->chunk(2) as $pair)
                    <tr>
                        @foreach($pair as $child)
                            @php
                                // Count optional fields to decide font size
                                $fieldCount = 2; // gender + age always present
                                if ($child->clothing_options) $fieldCount++;
                                if ($child->clothing_styles) $fieldCount++;
                                if ($child->all_sizes) $fieldCount++;
                                if ($child->toy_ideas) $fieldCount++;
                                if ($child->gift_preferences) $fieldCount++;
                                $compact = $fieldCount > 4;
                            @endphp
                            <td class="card">
                                <div class="card-header">
                                    <div class="card-number">#{{ $child->family->family_number }}</div>
                                    @if(isset($qrCodes[$child->id]))
                                        <div class="card-qr">
                                            <img src="{{ $qrCodes[$child->id] }}" alt="QR">
                                        </div>
                                    @endif
                                </div>

                                <div class="card-fields {{ $compact ? 'compact' : '' }}">
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

                                <div class="card-footer">
                                    Please bring in all gifts <u>UNWRAPPED</u> with this tag attached.<br>
                                    <span class="email">Questions? Email: fooddrive@gfalls.wednet.edu</span>
                                </div>
                            </td>
                        @endforeach
                        @if($pair->count() === 1)
                            <td class="card" style="border-color: transparent;"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endif
</body>
</html>
