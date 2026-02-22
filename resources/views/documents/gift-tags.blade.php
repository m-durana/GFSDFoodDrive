<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Tags</title>
    <style>
        /* Avery 8163 shipping labels: 2" x 4", 10 per page (2 columns, 5 rows) */
        /* Letter paper: 8.5" x 11" */
        @page {
            size: letter;
            margin: 0.5in 0.15in;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        .labels-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        .labels-table td {
            width: 50%;
            height: 2in;
            padding: 0.1in 0.15in;
            vertical-align: top;
            overflow: hidden;
            border: 1px dashed #ccc; /* guides for cutting — remove for production */
        }

        .label-content {
            height: 100%;
        }

        .family-number {
            font-size: 18pt;
            font-weight: bold;
            float: right;
            color: #333;
        }

        .child-info {
            font-size: 9pt;
            line-height: 1.3;
        }

        .child-info .field-label {
            font-weight: bold;
            color: #555;
        }

        .child-info .field-value {
            color: #000;
        }

        /* Force page break after every 10 labels (5 rows of 2) */
        .labels-table tr:nth-child(5n+1) {
            page-break-before: auto;
        }
    </style>
</head>
<body>
    @if($children->count() === 0)
        <p style="text-align: center; padding: 2in; font-size: 14pt; color: #666;">No children match the selected filter.</p>
    @else
        <table class="labels-table">
            @foreach($children->chunk(2) as $rowIndex => $pair)
                {{-- Start new page every 5 rows --}}
                @if($rowIndex > 0 && $rowIndex % 5 === 0)
                    </table>
                    <div style="page-break-before: always;"></div>
                    <table class="labels-table">
                @endif
                <tr>
                    @foreach($pair as $child)
                        <td>
                            <div class="label-content">
                                <div class="family-number">#{{ $child->family->family_number }}</div>
                                <div class="child-info">
                                    <div><span class="field-label">{{ $child->gender }}</span>, Age <span class="field-value">{{ $child->age }}</span></div>
                                    @if($child->clothing_options)
                                        <div><span class="field-label">Clothing:</span> <span class="field-value">{{ $child->clothing_options }}</span></div>
                                    @endif
                                    @if($child->clothing_styles)
                                        <div><span class="field-label">Styles:</span> <span class="field-value">{{ $child->clothing_styles }}</span></div>
                                    @endif
                                    @if($child->all_sizes)
                                        <div><span class="field-label">Sizes:</span> <span class="field-value">{{ $child->all_sizes }}</span></div>
                                    @endif
                                    @if($child->toy_ideas)
                                        <div><span class="field-label">Toy Ideas:</span> <span class="field-value">{{ $child->toy_ideas }}</span></div>
                                    @endif
                                    @if($child->gift_preferences)
                                        <div><span class="field-label">Gift Pref:</span> <span class="field-value">{{ $child->gift_preferences }}</span></div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    @endforeach
                    {{-- Fill empty cell if odd number --}}
                    @if($pair->count() === 1)
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
