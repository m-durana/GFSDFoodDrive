<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Day Sheets</title>
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

        .family-section {
            margin-bottom: 20pt;
            padding: 12pt;
            border: 1pt solid #999;
            page-break-inside: avoid;
        }

        .family-header {
            border-bottom: 1pt solid #ccc;
            padding-bottom: 6pt;
            margin-bottom: 8pt;
        }

        .family-number {
            font-size: 16pt;
            font-weight: bold;
            float: right;
            color: #c00;
        }

        .family-name {
            font-size: 14pt;
            font-weight: bold;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 3pt 8pt;
            vertical-align: top;
        }

        .info-grid .label {
            font-weight: bold;
            width: 25%;
            color: #555;
        }

        .info-grid .value {
            width: 75%;
        }

        .warning {
            background: #fff3cd;
            border: 1pt solid #ffc107;
            padding: 4pt 8pt;
            margin-top: 6pt;
            font-size: 10pt;
        }

        .pet-warning {
            background: #f8d7da;
            border: 1pt solid #f5c6cb;
            padding: 4pt 8pt;
            margin-top: 6pt;
            font-size: 10pt;
            font-weight: bold;
        }

        .delivery-status {
            float: right;
            font-size: 9pt;
            padding: 2pt 8pt;
            border: 1pt solid #ccc;
            background: #f9f9f9;
        }

        .checkbox-line {
            margin-top: 8pt;
            font-size: 10pt;
            color: #555;
        }

        .checkbox-line span {
            display: inline-block;
            width: 12pt;
            height: 12pt;
            border: 1pt solid #333;
            margin-right: 4pt;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    @forelse($families as $family)
        <div class="family-section">
            <div class="family-header">
                <div class="family-number">#{{ $family->family_number }}</div>
                @if($family->delivery_status)
                    <div class="delivery-status">{{ $family->delivery_status->label() }}</div>
                @endif
                <div class="family-name">{{ $family->family_name }}</div>
            </div>

            <table class="info-grid">
                <tr>
                    <td class="label">Address:</td>
                    <td class="value">{{ $family->address }}</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td class="value">{{ $family->phone1 }}</td>
                </tr>
                @if($family->phone2)
                    <tr>
                        <td class="label">Alt Phone:</td>
                        <td class="value">{{ $family->phone2 }}</td>
                    </tr>
                @endif
                @if($family->delivery_preference)
                    <tr>
                        <td class="label">Preference:</td>
                        <td class="value">{{ $family->delivery_preference }}</td>
                    </tr>
                @endif
                @if($family->delivery_date || $family->delivery_time)
                    <tr>
                        <td class="label">Scheduled:</td>
                        <td class="value">{{ $family->delivery_date }} {{ $family->delivery_time }}</td>
                    </tr>
                @endif
                @if($family->delivery_team)
                    <tr>
                        <td class="label">Team:</td>
                        <td class="value">{{ $family->delivery_team }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Members:</td>
                    <td class="value">{{ $family->number_of_family_members }} ({{ $family->number_of_adults }} adults, {{ $family->number_of_children }} children)</td>
                </tr>
            </table>

            @if($family->delivery_reason)
                <div class="warning">
                    <strong>Cannot deliver because:</strong> {{ $family->delivery_reason }}
                </div>
            @endif

            @if($family->pet_information)
                <div class="pet-warning">
                    PETS: {{ $family->pet_information }}
                </div>
            @endif

            @if($family->preferred_language && $family->preferred_language !== 'English')
                <div class="warning">
                    <strong>Language:</strong> {{ $family->preferred_language }}
                </div>
            @endif

            <div class="checkbox-line">
                <span></span> Delivered &nbsp;&nbsp;
                <span></span> Left at door &nbsp;&nbsp;
                <span></span> No answer &nbsp;&nbsp;
                <span></span> Notes: ___________________________
            </div>
        </div>
    @empty
        <p style="text-align: center; padding: 2in; font-size: 14pt; color: #666;">No families match the selected filter.</p>
    @endforelse
</body>
</html>
