<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags — GFSD Food Drive</title>
    <style>
        /* Avery 8163 — 2 columns x 3 rows = 6 labels per letter page */
        @page {
            size: letter;
            margin: 0.5in;
        }

        body {
            font-family: 'Century Gothic', 'Trebuchet MS', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .cards-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cards-table td.card {
            width: 48%;
            height: 3.0in;
            vertical-align: top;
            padding: 0.1in 0.15in;
            border: 1px dashed #bbb;
            overflow: hidden;
        }

        .cards-table td.spacer {
            width: 4%;
        }

        .card-number {
            font-size: 24pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4pt;
            color: #000;
        }

        .card-field {
            font-size: 11pt;
            line-height: 1.4;
            margin-bottom: 2pt;
        }

        .card-field .label {
            font-weight: bold;
            font-size: 11pt;
        }

        .card-field .value {
            font-size: 11pt;
        }

        .card-footer {
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            margin-top: 6pt;
            padding-top: 4pt;
            border-top: 1px solid #999;
            color: #333;
            line-height: 1.3;
        }

        .card-footer .email {
            font-weight: normal;
            font-style: italic;
            font-size: 7pt;
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
        @foreach($children->chunk(2) as $rowIndex => $pair)
            @if($rowIndex > 0 && $rowIndex % 3 === 0)
                <div class="page-break"></div>
            @endif
            <table class="cards-table">
                <tr>
                    @foreach($pair as $child)
                        <td class="card">
                            <table style="width: 100%; border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: top; padding: 0; border: none;">
                                        <div class="card-number">#{{ $child->family->family_number }}</div>
                                    </td>
                                    <td style="width: 1.0in; vertical-align: top; text-align: right; padding: 0; border: none;">
                                        @if(isset($qrCodes[$child->id]))
                                            <img src="{{ $qrCodes[$child->id] }}" alt="QR" style="width: 0.9in; height: 0.9in;">
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div class="card-field">
                                <span class="label">Gender:</span> <span class="value">{{ $child->gender }}</span>
                            </div>
                            <div class="card-field">
                                <span class="label">Age:</span> <span class="value">{{ $child->age }}</span>
                            </div>
                            @if($child->clothing_options)
                                <div class="card-field">
                                    <span class="label">Clothing:</span> <span class="value">{{ $child->clothing_options }}</span>
                                </div>
                            @endif
                            @if($child->clothing_styles)
                                <div class="card-field">
                                    <span class="label">Styles:</span> <span class="value">{{ $child->clothing_styles }}</span>
                                </div>
                            @endif
                            @if($child->all_sizes)
                                <div class="card-field">
                                    <span class="label">Sizes:</span> <span class="value">{{ $child->all_sizes }}</span>
                                </div>
                            @endif
                            @if($child->toy_ideas)
                                <div class="card-field">
                                    <span class="label">Toy Ideas:</span> <span class="value">{{ $child->toy_ideas }}</span>
                                </div>
                            @endif
                            @if($child->gift_preferences)
                                <div class="card-field">
                                    <span class="label">Gift Preferences:</span> <span class="value">{{ $child->gift_preferences }}</span>
                                </div>
                            @endif

                            <div class="card-footer">
                                Please bring in all gifts <u>UNWRAPPED</u><br>
                                with this tag attached.<br>
                                <span class="email">Questions? Email: fooddrive@gfalls.wednet.edu</span>
                            </div>
                        </td>
                        @if($loop->first && $pair->count() > 1)
                            <td class="spacer"></td>
                        @endif
                    @endforeach
                    @if($pair->count() === 1)
                        <td class="spacer"></td>
                        <td class="card" style="border-color: transparent;"></td>
                    @endif
                </tr>
            </table>
        @endforeach
    @endif
</body>
</html>
