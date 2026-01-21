@extends('layout')

@section('title', 'Dashboard')

@section('content')
    <style>
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi-card {
            background: var(--card);
            border-radius: 14px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .kpi-label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            margin-top: 6px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }
        .card-title {
            font-family: "Trebuchet MS", "Lucida Grande", sans-serif;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
        }
        .donut {
            --size: 150px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e5e7eb 0);
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
        }
        .donut-center {
            width: 96px;
            height: 96px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8px;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }
        .donut-value {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }
        .pointku-card {
            text-align: center;
        }
        .kpi-pair {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .kpi-pair strong {
            display: block;
            margin-top: 4px;
        }
        .progress-list {
            display: grid;
            gap: 12px;
        }
        .progress-row {
            display: grid;
            grid-template-columns: 1fr 1.2fr 36px;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .progress-bar {
            background: #e5e7eb;
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
        }
        .progress-bar span {
            display: block;
            height: 100%;
            border-radius: 999px;
        }
        .progress-blue span { background: #12b5c9; }
        .progress-orange span { background: #f97316; }
        .progress-red span { background: #ef4444; }
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 150px;
            padding: 10px 6px 24px;
            border-bottom: 1px dashed var(--border);
        }
        .bar {
            flex: 1;
            min-width: 26px;
            border-radius: 8px 8px 0 0;
            position: relative;
        }
        .bar span {
            position: absolute;
            bottom: -22px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: var(--muted);
            white-space: nowrap;
        }
        .bar:nth-child(odd) { background: #12b5c9; }
        .bar:nth-child(even) { background: #3f475d; }
        .status-list {
            display: grid;
            gap: 10px;
            font-size: 13px;
        }
        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .status-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
        }
        .status-dot.pending { background: #f97316; }
        .status-dot.verified { background: #12b5c9; }
        .status-dot.total { background: #3f475d; }
        .revenue-box {
            background: #e6f7fa;
            border-radius: 14px;
            padding: 18px;
            display: grid;
            gap: 6px;
            text-align: center;
        }
        .revenue-value {
            font-size: 26px;
            font-weight: 700;
            color: #0b5f57;
        }
        .alert-list {
            display: grid;
            gap: 8px;
            font-size: 13px;
        }
        .alert-item {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .heatmap-card {
            margin: 18px 0;
        }
        .section-block {
            margin: 18px 0;
        }
        .heatmap {
            display: grid;
            gap: 10px;
        }
        .heatmap-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }
        .heatmap-months {
            display: flex;
            gap: 4px;
            margin-left: 38px;
            font-size: 11px;
            color: var(--muted);
        }
        .heatmap-months span {
            width: 12px;
            text-align: center;
        }
        .heatmap-grid {
            display: grid;
            grid-template-columns: 38px 1fr;
            gap: 8px;
        }
        .heatmap-scroll {
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .heatmap-scroll .heatmap-months,
        .heatmap-scroll .heatmap-grid {
            min-width: 680px;
        }
        .heatmap-days {
            display: grid;
            grid-template-rows: repeat(7, 12px);
            gap: 4px;
            font-size: 10px;
            color: var(--muted);
        }
        .heatmap-days span {
            display: flex;
            align-items: center;
            height: 12px;
        }
        .heatmap-weeks {
            display: flex;
            gap: 4px;
        }
        .heatmap-week {
            display: grid;
            grid-template-rows: repeat(7, 12px);
            gap: 4px;
        }
        .heatmap-day {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            background: #e5e7eb;
        }
        .heatmap-day.level-0 { background: #e5e7eb; }
        .heatmap-day.level-1 { background: #c7ecef; }
        .heatmap-day.level-2 { background: #7dd3de; }
        .heatmap-day.level-3 { background: #22b8cf; }
        .heatmap-day.level-4 { background: #0ea5b7; }
        .heatmap-day.empty { background: transparent; }
        .heatmap-legend {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--muted);
        }
        .heatmap-legend .legend-dots {
            display: flex;
            gap: 4px;
        }
        .heatmap-legend .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
    </style>

    <h1>Dashboard</h1>
    <p class="muted">Ringkasan performa tim dan status validasi konten.</p>

    <div class="card heatmap-card section-block">
        <div class="heatmap-header">
            <h2>Aktivitas 12 Bulan Terakhir</h2>
            <span class="muted">{{ $heatmap['total'] }} kontribusi</span>
        </div>
        <div class="heatmap">
            <div class="heatmap-scroll">
                <div class="heatmap-months">
                    @foreach ($heatmap['weeks'] as $index => $week)
                        <span>{{ $heatmap['month_labels'][$index] ?? '' }}</span>
                    @endforeach
                </div>
                <div class="heatmap-grid">
                    <div class="heatmap-days">
                        <span>Mon</span>
                        <span></span>
                        <span>Wed</span>
                        <span></span>
                        <span>Fri</span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="heatmap-weeks">
                        @foreach ($heatmap['weeks'] as $week)
                            <div class="heatmap-week">
                                @foreach ($week as $day)
                                    @php($title = $day['in_range'] ? $day['date'].': '.$day['count'].' aktivitas' : '')
                                    <span class="heatmap-day level-{{ $day['level'] }} {{ $day['in_range'] ? '' : 'empty' }}"
                                          title="{{ $title }}"></span>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="heatmap-legend">
                <span>Less</span>
                <div class="legend-dots">
                    <span class="legend-dot" style="background: #e5e7eb;"></span>
                    <span class="legend-dot" style="background: #c7ecef;"></span>
                    <span class="legend-dot" style="background: #7dd3de;"></span>
                    <span class="legend-dot" style="background: #22b8cf;"></span>
                    <span class="legend-dot" style="background: #0ea5b7;"></span>
                </div>
                <span>More</span>
            </div>
        </div>
    </div>

    @if (auth()->user()->role === 'leader' && $teamMemberPoints->isNotEmpty())
        <div class="card section-block">
            <h2>Perolehan Poin Tim</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Poin Aktivitas</th>
                        <th>Poin Konversi</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamMemberPoints as $member)
                        <tr>
                            <td>
                                <a href="{{ route('profile.view', $member) }}">{{ $member->name }}</a>
                            </td>
                            <td>{{ strtoupper($member->role) }}</td>
                            <td>{{ number_format($member->activity_points, 2) }}</td>
                            <td>{{ number_format($member->conversion_points, 2) }}</td>
                            <td><strong>{{ number_format($member->total_points, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="dashboard-grid section-block">
        <div class="card pointku-card">
            <div class="card-title">Pointku</div>
            <div class="donut" style="--percent: {{ $pointPercent }};">
                <div class="donut-center">
                    <div class="donut-value">{{ number_format($totalPoints, 0) }}</div>
                </div>
            </div>
            <div class="kpi-pair">
                <div>
                    <div class="muted">Aktivitas</div>
                    <strong>{{ number_format($activityPoints, 2) }}</strong>
                </div>
                <div>
                    <div class="muted">Konversi</div>
                    <strong>{{ number_format($conversionPoints, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Leads Harian</div>
            <div class="bar-chart">
                @foreach ($leadSeries as $lead)
                    @php($height = $leadMax > 0 ? round(($lead['count'] / $leadMax) * 100) : 0)
                    <div class="bar" style="height: {{ max($height, 6) }}%;">
                        <span>{{ $lead['date'] }}</span>
                    </div>
                @endforeach
            </div>
            @php($leadTotal = array_sum(array_column($leadSeries, 'count')))
            <p class="muted">Total 5 hari: {{ $leadTotal }} lead masuk.</p>
        </div>

        <div class="card">
            <div class="card-title">Jumlah Closing</div>
            <div class="revenue-box">
                <div class="revenue-value">{{ number_format($closingTotal, 0, ',', '.') }}</div>
                <div class="muted">Closing terverifikasi</div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Jumlah Leads</div>
            <div class="revenue-box">
                <div class="revenue-value">{{ number_format($leadTotal, 0, ',', '.') }}</div>
                <div class="muted">Leads terverifikasi</div>
            </div>
        </div>
    </div>
@endsection
