<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Family Summary Sheets</title>
    <style>
        @page {
            size: letter;
            margin: 0.75in;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .family-page {
            page-break-after: always;
        }

        .family-page:last-child {
            page-break-after: auto;
        }

        h1 {
            font-size: 18pt;
            margin: 0 0 5pt 0;
            border-bottom: 2pt solid #333;
            padding-bottom: 5pt;
        }

        .family-number {
            font-size: 24pt;
            float: right;
            font-weight: bold;
            color: #c00;
        }

        .section {
            margin: 12pt 0;
        }

        .section h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 6pt 0;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3pt 8pt;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 40%;
            color: #555;
        }

        .info-table .value {
            width: 60%;
        }

        .demographics-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1pt solid #ccc;
        }

        .demographics-grid th,
        .demographics-grid td {
            border: 1pt solid #ccc;
            padding: 4pt 8pt;
            text-align: center;
        }

        .demographics-grid th {
            background-color: #f0f0f0;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .demographics-grid td {
            font-size: 14pt;
            font-weight: bold;
        }

        .flag {
            display: inline-block;
            padding: 2pt 6pt;
            background: #ffeeba;
            border-radius: 3pt;
            font-size: 9pt;
            font-weight: bold;
            margin: 2pt 2pt 2pt 0;
        }
    </style>
</head>
<body>
    @forelse($families as $family)
        <div class="family-page">
            <div class="family-number">#{{ $family->family_number }}</div>
            <h1>{{ $family->family_name }}</h1>

            <div class="section">
                <h2>Demographics</h2>
                <table class="demographics-grid">
                    <tr>
                        <th>Total Members</th>
                        <th>Adults</th>
                        <th>Children</th>
                        <th>Infants (0-2)</th>
                        <th>Young (3-7)</th>
                        <th>Children (8-12)</th>
                        <th>Tweens (13-14)</th>
                        <th>Teens (15-17)</th>
                    </tr>
                    <tr>
                        <td>{{ $family->number_of_family_members }}</td>
                        <td>{{ $family->number_of_adults }}</td>
                        <td>{{ $family->number_of_children }}</td>
                        <td>{{ $family->infants }}</td>
                        <td>{{ $family->young_children }}</td>
                        <td>{{ $family->children_count }}</td>
                        <td>{{ $family->tweens }}</td>
                        <td>{{ $family->teenagers }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                @if($family->needs_baby_supplies)
                    <span class="flag">NEEDS BABY SUPPLIES / FOOD</span>
                @endif
                @if($family->has_crhs_children)
                    <span class="flag">CRHS CHILDREN</span>
                @endif
                @if($family->has_gfhs_children)
                    <span class="flag">GFHS CHILDREN</span>
                @endif
                @if($family->severe_need)
                    <span class="flag" style="background: #f5c6cb;">SEVERE NEED</span>
                @endif
            </div>

            <div class="section">
                <h2>Contact</h2>
                <table class="info-table">
                    <tr><td class="label">Address:</td><td class="value">{{ $family->address }}</td></tr>
                    <tr><td class="label">Phone:</td><td class="value">{{ $family->phone1 }}{{ $family->phone2 ? ' / ' . $family->phone2 : '' }}</td></tr>
                    @if($family->email)<tr><td class="label">Email:</td><td class="value">{{ $family->email }}</td></tr>@endif
                    @if($family->preferred_language && $family->preferred_language !== 'English')<tr><td class="label">Language:</td><td class="value">{{ $family->preferred_language }}</td></tr>@endif
                </table>
            </div>

            @if($family->need_for_help || $family->severe_need)
                <div class="section">
                    <h2>Needs</h2>
                    <table class="info-table">
                        @if($family->need_for_help)<tr><td class="label">Reason for Help:</td><td class="value">{{ $family->need_for_help }}</td></tr>@endif
                        @if($family->severe_need)<tr><td class="label">Severe Need:</td><td class="value">{{ $family->severe_need }}</td></tr>@endif
                    </table>
                </div>
            @endif

            @if($family->pet_information)
                <div class="section">
                    <h2>Pets</h2>
                    <p>{{ $family->pet_information }}</p>
                </div>
            @endif
        </div>
    @empty
        <p style="text-align: center; padding: 2in; font-size: 14pt; color: #666;">No families match the selected filter.</p>
    @endforelse
</body>
</html>
