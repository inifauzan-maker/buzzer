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
            display: grid;
            place-items: center;
            text-align: center;
            padding: 8px;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }
        .donut-value {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
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
    </style>

    <h1>Dashboard</h1>
    <p class="muted">Ringkasan performa tim dan status validasi konten.</p>

    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-label">Total Tim</div>
            <div class="kpi-value">{{ $totalTeams }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total User</div>
            <div class="kpi-value">{{ $totalUsers }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Pending Aktivitas</div>
            <div class="kpi-value">{{ $pendingActivities }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Pending Konversi</div>
            <div class="kpi-value">{{ $pendingConversions }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-title">Pointku</div>
            <div class="donut" style="--percent: {{ $pointPercent }};">
                <div class="donut-center">
                    <div class="donut-value">{{ number_format($totalPoints, 0) }}</div>
                    <div class="muted">Target {{ $pointTarget }}</div>
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
            <div class="card-title">Completed Tasks</div>
            <div class="progress-list">
                <div class="progress-row">
                    <span>Aktivitas</span>
                    <div class="progress-bar progress-blue">
                        <span style="width: {{ $activityCompletion }}%;"></span>
                    </div>
                    <strong>{{ $verifiedActivities }}</strong>
                </div>
                <div class="progress-row">
                    <span>Konversi</span>
                    <div class="progress-bar progress-orange">
                        <span style="width: {{ $conversionCompletion }}%;"></span>
                    </div>
                    <strong>{{ $verifiedConversions }}</strong>
                </div>
                <div class="progress-row">
                    <span>Pending</span>
                    <div class="progress-bar progress-red">
                        <span style="width: {{ $pendingPercent }}%;"></span>
                    </div>
                    <strong>{{ $pendingTotal }}</strong>
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
            <div class="card-title">Status Verifikasi</div>
            <div class="status-list">
                <div class="status-item">
                    <div class="status-left">
                        <span class="status-dot pending"></span>
                        Pending Aktivitas
                    </div>
                    <strong>{{ $pendingActivities }}</strong>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <span class="status-dot pending"></span>
                        Pending Konversi
                    </div>
                    <strong>{{ $pendingConversions }}</strong>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <span class="status-dot verified"></span>
                        Verified Aktivitas
                    </div>
                    <strong>{{ $verifiedActivities }}</strong>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <span class="status-dot verified"></span>
                        Verified Konversi
                    </div>
                    <strong>{{ $verifiedConversions }}</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Revenue Closing</div>
            <div class="revenue-box">
                <div class="revenue-value">Rp {{ number_format($closingTotal, 0, ',', '.') }}</div>
                <div class="muted">Closing terverifikasi</div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Team Alerts</div>
            <div class="alert-list">
                @forelse ($inactiveTeams as $team)
                    @php($lastDate = $team->last_post_date ? \Illuminate\Support\Carbon::parse($team->last_post_date)->format('d M') : '-')
                    <div class="alert-item">
                        <span>{{ $team->team_name }}</span>
                        <span class="muted">{{ $lastDate }}</span>
                    </div>
                @empty
                    <div class="muted">Semua tim aktif 2 hari terakhir.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Top Leaderboard</h2>
        <table>
            <thead>
                <tr>
                    <th>Rangking</th>
                    <th>Tim</th>
                    <th>Poin Aktivitas</th>
                    <th>Poin Konversi</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaderboard as $index => $team)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td>{{ $team->team_name }}</td>
                        <td>{{ number_format($team->activity_points, 2) }}</td>
                        <td>{{ number_format($team->conversion_points, 2) }}</td>
                        <td><strong>{{ number_format($team->total_points, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">Belum ada poin yang diverifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
