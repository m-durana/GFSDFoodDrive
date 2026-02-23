<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Family Summary Sheets — GFSD Food Drive</title>
    <style>
        /* Matches original 708.docx - Franklin Gothic Heavy, large centered number */
        @page {
            size: letter;
            margin: 0.5in 0.75in;
        }

        body {
            font-family: 'Franklin Gothic Heavy', 'Arial Black', 'Impact', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .family-page {
            page-break-after: always;
            text-align: center;
        }

        .family-page:last-child {
            page-break-after: auto;
        }

        .big-number {
            font-size: 100pt;
            font-weight: bold;
            text-align: center;
            margin: 0;
            padding: 20pt 0 10pt 0;
            line-height: 1;
        }

        .big-number-label {
            font-size: 28pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10pt;
        }

        .summary-line {
            font-size: 28pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10pt 0;
            text-align: center;
        }

        .divider {
            border: none;
            border-top: 3pt solid #000;
            margin: 15pt 0;
        }

        .age-section {
            text-align: center;
            margin: 8pt 0;
        }

        .age-label {
            font-size: 24pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .age-count {
            font-size: 36pt;
            font-weight: bold;
            margin: 5pt 0 15pt 0;
        }

        .info-line {
            font-size: 24pt;
            font-weight: bold;
            text-align: center;
            margin: 10pt 0;
        }

        .info-line .field-value {
            text-decoration: underline;
            border-bottom: 2pt solid #000;
            padding-bottom: 2pt;
        }

        .boxes-section {
            margin-top: 30pt;
            text-align: center;
        }

        .boxes-label {
            font-size: 28pt;
            font-weight: bold;
        }

        .boxes-line {
            display: inline-block;
            width: 120pt;
            border-bottom: 3pt solid #000;
            margin-left: 10pt;
        }
    </style>
</head>
<body>
    @forelse($families as $family)
        <div class="family-page">
            <div class="big-number-label">Family Number</div>
            <div class="big-number">{{ $family->family_number }}</div>

            <div class="summary-line">
                Total Family Members: {{ $family->number_of_family_members }}
            </div>

            <div class="summary-line">
                Number of Children: {{ $family->number_of_children }}
            </div>

            <hr class="divider">

            <div class="age-section">
                <div class="age-label">Infants (0-2)</div>
                <div class="age-count">{{ $family->infants }}</div>
            </div>

            <div class="age-section">
                <div class="age-label">Young Child (3-7)</div>
                <div class="age-count">{{ $family->young_children }}</div>
            </div>

            <div class="age-section">
                <div class="age-label">Child (8-12)</div>
                <div class="age-count">{{ $family->children_count }}</div>
            </div>

            <div class="age-section">
                <div class="age-label">Tween (13-14)</div>
                <div class="age-count">{{ $family->tweens }}</div>
            </div>

            <div class="age-section">
                <div class="age-label">Teenager (15-17)</div>
                <div class="age-count">{{ $family->teenagers }}</div>
            </div>

            <hr class="divider">

            <div class="info-line">
                Baby Food Needed? <span class="field-value">{{ $family->needs_baby_supplies ? 'YES' : 'No' }}</span>
            </div>

            <div class="boxes-section">
                <span class="boxes-label"># of Boxes:</span>
                <span class="boxes-line">&nbsp;</span>
            </div>
        </div>
    @empty
        <p style="text-align: center; padding: 3in 0; font-size: 18pt; color: #666;">No families match the selected filter.</p>
    @endforelse
</body>
</html>
