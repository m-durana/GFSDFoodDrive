<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Lists - Batch Print</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; }
        .page { padding: 10mm; max-width: 210mm; margin: 0 auto; page-break-after: always; }
        .page:last-child { page-break-after: auto; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6mm; border-bottom: 2px solid #000; padding-bottom: 3mm; }
        .header-left h1 { font-size: 14pt; margin-bottom: 1mm; }
        .header-left p { font-size: 9pt; color: #555; }
        .header-right img { width: 25mm; height: 25mm; }
        .header-right .barcode { display: block; margin-top: 1mm; width: 25mm; height: auto; }

        .info-bar { display: flex; gap: 3mm; margin-bottom: 4mm; font-size: 8pt; }
        .info-bar .badge { background: #f0f0f0; padding: 1.5mm 3mm; border-radius: 1mm; }
        .info-bar .badge.severe { background: #fee; color: #c00; font-weight: bold; }

        .section h2 { font-size: 10pt; margin-bottom: 2mm; padding: 1.5mm 2mm; background: #f5f5f5; border-left: 2px solid #333; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 4mm; }
        th { text-align: left; padding: 1.5mm 2mm; font-size: 7pt; text-transform: uppercase; color: #555; border-bottom: 1px solid #999; }
        td { padding: 1.5mm 2mm; border-bottom: 1px solid #eee; }
        .checkbox { width: 4mm; height: 4mm; border: 1px solid #333; display: inline-block; }
        .qty { text-align: center; width: 10mm; }
        .num { text-align: center; width: 6mm; color: #888; font-size: 8pt; }

        .signature { margin-top: 4mm; display: flex; gap: 6mm; align-items: flex-end; font-size: 9pt; }
        .signature-line { flex: 1; border-bottom: 1px solid #333; height: 6mm; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        @media screen {
            .no-print-bar { position: fixed; top: 0; left: 0; right: 0; background: #333; color: #fff; padding: 8px 16px; text-align: center; z-index: 999; }
            .no-print-bar button { background: #fff; color: #333; border: none; padding: 6px 16px; border-radius: 4px; cursor: pointer; margin-left: 12px; font-weight: bold; }
            .page:first-child { margin-top: 50px; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar no-print">
        Batch Print: {{ $printData->count() }} packing lists
        <button onclick="window.print()">Print All</button>
        <button onclick="window.close()">Close</button>
    </div>

    @foreach($printData as $data)
        @php $list = $data['list']; @endphp
        <div class="page">
            <div class="header">
                <div class="header-left">
                    <h1>
                        Family #{{ $list->family?->family_number }}
                        @if(\App\Models\Setting::get('packing_show_names', '1') === '1')
                            | {{ $list->family?->family_name }}
                        @endif
                    </h1>
                    <p>Season {{ $list->season_year }}</p>
                </div>
                <div class="header-right">
                    <img src="{{ $data['qrCode'] }}" alt="QR">
                    <svg class="barcode" data-value="F{{ $list->family?->family_number ?? $list->id }}"></svg>
                </div>
            </div>

            <div class="info-bar">
                <span class="badge">Members: {{ $list->family?->number_of_family_members ?? '?' }}</span>
                <span class="badge">Children: {{ $list->family?->children?->count() ?? 0 }}</span>
                @if($list->family?->is_severe_need)
                    <span class="badge severe">SEVERE NEED</span>
                @endif
            </div>

            @if($data['foodItems']->count())
                <div class="section">
                    <h2>Food ({{ $data['foodItems']->count() }})</h2>
                    <table>
                        <tbody>
                            @foreach($data['foodItems'] as $i => $item)
                                <tr>
                                    <td class="num">{{ $i + 1 }}</td>
                                    <td><span class="checkbox"></span></td>
                                    <td>{{ $item->description }}</td>
                                    <td class="qty">x{{ $item->quantity_needed }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($data['giftItems']->count())
                <div class="section">
                    <h2>Gifts ({{ $data['giftItems']->count() }})</h2>
                    <table>
                        <tbody>
                            @foreach($data['giftItems'] as $i => $item)
                                <tr>
                                    <td class="num">{{ $i + 1 }}</td>
                                    <td><span class="checkbox"></span></td>
                                    <td>{{ $item->description }}</td>
                                    <td class="qty">x{{ $item->quantity_needed }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($data['babyItems']->count())
                <div class="section">
                    <h2>Baby ({{ $data['babyItems']->count() }})</h2>
                    <table>
                        <tbody>
                            @foreach($data['babyItems'] as $i => $item)
                                <tr>
                                    <td class="num">{{ $i + 1 }}</td>
                                    <td><span class="checkbox"></span></td>
                                    <td>{{ $item->description }}</td>
                                    <td class="qty">x{{ $item->quantity_needed }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="signature">
                <label>Packed by:</label><div class="signature-line"></div>
                <label>Verified by:</label><div class="signature-line"></div>
            </div>
        </div>
    @endforeach
    <script>
        document.querySelectorAll('.barcode').forEach(el => {
            try {
                JsBarcode(el, el.dataset.value, { format: 'CODE128', width: 1, height: 25, displayValue: true, fontSize: 8, margin: 0 });
            } catch (e) {}
        });
    </script>
</body>
</html>
