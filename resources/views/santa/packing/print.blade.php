<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing List - Family #{{ $packingList->family?->family_number }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; }

        .page { padding: 15mm; max-width: 210mm; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8mm; border-bottom: 2px solid #000; padding-bottom: 4mm; }
        .header-left h1 { font-size: 16pt; margin-bottom: 2mm; }
        .header-left p { font-size: 10pt; color: #555; }
        .header-right { text-align: center; }
        .header-right img { width: 30mm; height: 30mm; }
        .header-right p { font-size: 7pt; color: #888; margin-top: 1mm; }
        .header-right .barcode { display: block; margin-top: 2mm; width: 30mm; height: auto; }

        .info-bar { display: flex; gap: 5mm; margin-bottom: 6mm; font-size: 9pt; }
        .info-bar .badge { background: #f0f0f0; padding: 2mm 4mm; border-radius: 2mm; }
        .info-bar .badge.severe { background: #fee; color: #c00; font-weight: bold; }

        .section { margin-bottom: 6mm; }
        .section h2 { font-size: 12pt; margin-bottom: 3mm; padding: 2mm 3mm; background: #f5f5f5; border-left: 3px solid #333; }

        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        th { text-align: left; padding: 2mm 3mm; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; color: #555; border-bottom: 1px solid #999; }
        td { padding: 2.5mm 3mm; border-bottom: 1px solid #ddd; }
        .checkbox { width: 5mm; height: 5mm; border: 1.5px solid #333; display: inline-block; vertical-align: middle; }
        .qty { text-align: center; width: 12mm; }
        .num { text-align: center; width: 8mm; color: #888; font-size: 9pt; }

        .footer { margin-top: 10mm; padding-top: 4mm; border-top: 1px solid #ccc; }
        .signature { margin-top: 6mm; display: flex; gap: 10mm; align-items: flex-end; }
        .signature-line { flex: 1; border-bottom: 1px solid #333; height: 8mm; }
        .signature label { font-size: 9pt; color: #555; white-space: nowrap; }

        .notes { margin-top: 4mm; padding: 3mm; border: 1px dashed #999; font-size: 9pt; color: #555; min-height: 10mm; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { padding: 10mm; }
            .no-print { display: none !important; }
        }

        @media screen {
            .no-print-bar { position: fixed; top: 0; left: 0; right: 0; background: #333; color: #fff; padding: 8px 16px; text-align: center; z-index: 999; font-size: 14px; }
            .no-print-bar button { background: #fff; color: #333; border: none; padding: 6px 16px; border-radius: 4px; cursor: pointer; margin-left: 12px; font-weight: bold; }
            .page { margin-top: 50px; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar no-print">
        Packing List Preview
        @if(isset($printType) && $printType !== 'both')
            &mdash; {{ ucfirst($printType) }} Items Only
        @endif
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="page">
        <div class="header">
            <div class="header-left">
                <h1>Packing List</h1>
                <p>
                    Family #{{ $packingList->family?->family_number }}
                    @if(\App\Models\Setting::get('packing_show_names', '1') === '1')
                        | {{ $packingList->family?->family_name }}
                    @endif
                </p>
                <p>Season {{ $packingList->season_year }}</p>
            </div>
            <div class="header-right">
                <img src="{{ $qrCode }}" alt="QR Code">
                <p>Scan to open digital list</p>
                <svg class="barcode" data-value="F{{ $packingList->family?->family_number ?? $packingList->id }}"></svg>
            </div>
        </div>

        <div class="info-bar">
            <span class="badge">Members: {{ $packingList->family?->number_of_family_members ?? '?' }}</span>
            <span class="badge">Children: {{ $packingList->family?->children?->count() ?? 0 }}</span>
            <span class="badge">Boxes: {{ $packingList->family?->number_of_boxes ?? 1 }}</span>
            @if($packingList->family?->is_severe_need)
                <span class="badge severe">SEVERE NEED</span>
            @endif
            @if($packingList->family?->needs_baby_supplies)
                <span class="badge">Baby Supplies</span>
            @endif
        </div>

        @if($packingList->notes)
            <div class="notes">
                <strong>Notes:</strong> {{ $packingList->notes }}
            </div>
        @endif

        <!-- Food Items -->
        @if($foodItems->count())
            <div class="section">
                <h2>Food Items ({{ $foodItems->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th></th>
                            <th>Category</th>
                            <th>Item</th>
                            <th class="qty">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($foodItems as $i => $item)
                            <tr>
                                <td class="num">{{ $i + 1 }}</td>
                                <td><span class="checkbox"></span></td>
                                <td>{{ $item->category?->name ?? '' }}</td>
                                <td>
                                    {{ $item->description }}
                                    @if($item->warehouseItem && $item->warehouseItem->locationLabel() !== 'Unassigned')
                                        <span style="color:#999;font-size:0.75em;margin-left:4px">{{ $item->warehouseItem->locationLabel() }}</span>
                                    @endif
                                </td>
                                <td class="qty">{{ $item->quantity_needed }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Gift Items -->
        @if($giftItems->count())
            <div class="section">
                <h2>Gifts ({{ $giftItems->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th></th>
                            <th>Child</th>
                            <th>Description</th>
                            <th class="qty">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($giftItems as $i => $item)
                            <tr>
                                <td class="num">{{ $i + 1 }}</td>
                                <td><span class="checkbox"></span></td>
                                <td>
                                    @if($item->child)
                                        {{ $item->child->gender ?? '' }} age {{ $item->child->age }}
                                    @endif
                                </td>
                                <td>{{ $item->description }}</td>
                                <td class="qty">{{ $item->quantity_needed }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Baby Supplies -->
        @if($babyItems->count())
            <div class="section">
                <h2>Baby Supplies ({{ $babyItems->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th></th>
                            <th>Item</th>
                            <th class="qty">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($babyItems as $i => $item)
                            <tr>
                                <td class="num">{{ $i + 1 }}</td>
                                <td><span class="checkbox"></span></td>
                                <td>{{ $item->description }}</td>
                                <td class="qty">{{ $item->quantity_needed }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="footer">
            <div class="signature">
                <label>Packed by:</label>
                <div class="signature-line"></div>
                <label>Date:</label>
                <div class="signature-line" style="flex: 0.5;"></div>
            </div>
            <div class="signature" style="margin-top: 4mm;">
                <label>Verified by:</label>
                <div class="signature-line"></div>
                <label>Date:</label>
                <div class="signature-line" style="flex: 0.5;"></div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.barcode').forEach(el => {
            try {
                JsBarcode(el, el.dataset.value, { format: 'CODE128', width: 1, height: 30, displayValue: true, fontSize: 10, margin: 0 });
            } catch (e) {}
        });
    </script>
</body>
</html>
