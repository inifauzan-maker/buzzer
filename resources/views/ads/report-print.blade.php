<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ads Report</title>
        <style>
            body {
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                color: #1f2937;
                margin: 24px;
            }
            h1 {
                margin: 0 0 6px;
            }
            .muted {
                color: #6b7280;
                margin-bottom: 16px;
                font-size: 13px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }
            th, td {
                border: 1px solid #e5e7eb;
                padding: 6px 8px;
                text-align: left;
            }
            th {
                background: #f3f4f6;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.06em;
            }
            @media print {
                body { margin: 0; }
            }
        </style>
    </head>
    <body>
        <h1>Laporan Ads / Iklan</h1>
        <div class="muted">
            Periode:
            {{ $filters['start_date'] ?: 'Semua' }}
            -
            {{ $filters['end_date'] ?: 'Semua' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Campaign</th>
                    <th>Platform</th>
                    <th>PIC</th>
                    <th>Biaya</th>
                    <th>Tayangan</th>
                    <th>Jangkauan</th>
                    <th>Leads</th>
                    <th>Closing</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($metrics as $metric)
                    <tr>
                        <td>{{ $metric->report_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $metric->campaign?->name ?? '-' }}</td>
                        <td>{{ $metric->campaign?->platform ?? '-' }}</td>
                        <td>{{ $metric->pic?->name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $metric->cost, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $metric->impressions) }}</td>
                        <td>{{ number_format((int) $metric->reach) }}</td>
                        <td>{{ number_format((int) $metric->leads_count) }}</td>
                        <td>{{ number_format((int) $metric->closing_count) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Belum ada data laporan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
