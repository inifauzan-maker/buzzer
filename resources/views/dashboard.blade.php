@extends('layout')

@section('title', 'Dashboard')

@section('content')
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }
        .full-span {
            grid-column: 1 / -1;
        }
        .target-grid {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 18px;
        }
        .card-title {
            font-family: "Trebuchet MS", "Lucida Grande", sans-serif;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .card-header .card-title {
            margin-bottom: 0;
            text-align: left;
        }
        .card-subtitle {
            font-size: 12px;
            color: var(--muted);
        }
        .staff-kpis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin: 12px 0 16px;
        }
        .staff-kpi {
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 12px;
            display: grid;
            gap: 6px;
            text-align: center;
        }
        .staff-kpi strong {
            font-size: 16px;
        }
        .staff-rank {
            display: grid;
            gap: 10px;
            font-size: 13px;
        }
        .staff-row {
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 10px;
            align-items: center;
        }
        .staff-name {
            font-weight: 600;
        }
        .staff-bar {
            height: 8px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .staff-bar span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: #3f475d;
        }
        .lead-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .lead-filter select {
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 0 10px;
            background: #fff;
            font-size: 12px;
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
        .target-card {
            display: grid;
            gap: 12px;
        }
        .target-mini {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            display: grid;
            gap: 8px;
        }
        .target-mini-title {
            font-size: 13px;
            font-weight: 700;
        }
        .target-bar-chart {
            position: relative;
            height: 110px;
            padding: 10px 8px 14px 12px;
            display: grid;
            grid-template-rows: 1fr auto;
            gap: 6px;
            background: repeating-linear-gradient(
                to top,
                rgba(148, 163, 184, 0.25) 0,
                rgba(148, 163, 184, 0.25) 1px,
                transparent 1px,
                transparent 28px
            );
            border-left: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            border-radius: 8px;
        }
        .target-bar-stack {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 70px;
            justify-content: center;
        }
        .target-bar {
            width: 22px;
            border-radius: 6px 6px 4px 4px;
            background: #94a3b8;
        }
        .target-bar.achieved { background: #12b5c9; }
        .target-bar.target { background: #93c5fd; }
        .target-bar.leads { background: #f97316; }
        .target-bar-values {
            display: flex;
            justify-content: center;
            gap: 8px;
            font-size: 11px;
            color: var(--muted);
        }
        @media (max-width: 640px) {
            .target-grid {
                grid-template-columns: 1fr;
            }
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
        <div class="target-grid">
            <div class="card target-card">
                <div class="card-title">Target Closing {{ $targetYear }}</div>
                <div class="target-mini">
                    <div class="target-mini-title">{{ $targetLabel }}</div>
                    <div class="target-bar-chart">
                        <div class="target-bar-stack">
                            <div class="target-bar target" style="height: {{ $closingTargetHeight }}%;"></div>
                            <div class="target-bar achieved" style="height: {{ $closingAchievedHeight }}%;"></div>
                        </div>
                        <div class="target-bar-values">
                            <span>Target {{ number_format($targetClosing, 0, ',', '.') }}</span>
                            <span>Pencapaian {{ number_format($targetClosingAchieved, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="muted">{{ $targetClosingPercent }}% tercapai</div>
                </div>
            </div>
            <div class="card target-card">
                <div class="card-title">Target Leads {{ $targetYear }}</div>
                <div class="target-mini">
                    <div class="target-mini-title">{{ $targetLabel }}</div>
                    <div class="target-bar-chart">
                        <div class="target-bar-stack">
                            <div class="target-bar target" style="height: {{ $leadsTargetHeight }}%;"></div>
                            <div class="target-bar leads" style="height: {{ $leadsAchievedHeight }}%;"></div>
                        </div>
                        <div class="target-bar-values">
                            <span>Target {{ number_format($targetLeads, 0, ',', '.') }}</span>
                            <span>Pencapaian {{ number_format($targetLeadsAchieved, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="muted">{{ $targetLeadsPercent }}% tercapai</div>
                </div>
            </div>
        </div>

        <div class="card full-span">
            <div class="card-header">
                <div>
                    <div class="card-title">Performa Staff Bulan Ini</div>
                    <div class="card-subtitle">
                        {{ $leadMonthOptions[$leadMonth] ?? $leadMonth }} {{ $leadYear }}
                        @if (!empty($erUserLabel))
                            • {{ $erUserLabel }}
                        @endif
                    </div>
                </div>
                @if (!empty($erUserOptions) && $erUserOptions->isNotEmpty())
                    <form class="lead-filter" method="GET" action="{{ route('dashboard') }}">
                        <input type="hidden" name="lead_month" value="{{ $leadMonth }}">
                        <input type="hidden" name="lead_year" value="{{ $leadYear }}">
                        <select name="er_user_id" onchange="this.form.submit()">
                            <option value="0" @selected(empty($erUserId))>Semua Staff</option>
                            @foreach ($erUserOptions as $option)
                                <option value="{{ $option->id }}" @selected($erUserId == $option->id)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
            <div class="staff-kpis">
                <div class="staff-kpi">
                    <span class="muted">Total Poin</span>
                    <strong>{{ number_format($staffTotalPoints, 2) }}</strong>
                </div>
                <div class="staff-kpi">
                    <span class="muted">ER Rate</span>
                    <strong>{{ number_format($erRate, 2) }}%</strong>
                </div>
                <div class="staff-kpi">
                    <span class="muted">Closing / Leads</span>
                    <strong>{{ $staffClosingCount }} / {{ $staffLeadCount }}</strong>
                </div>
                <div class="staff-kpi">
                    <span class="muted">Hari Aktif</span>
                    <strong>{{ $staffActiveDays }} hari</strong>
                </div>
            </div>
            <div class="staff-rank">
                @forelse ($staffRankings as $staff)
                    @php($rankPercent = $staffRankMax > 0 ? round(($staff->total_points / $staffRankMax) * 100) : 0)
                    <div class="staff-row">
                        <div>
                            <div class="staff-name">{{ $staff->name }}</div>
                            <div class="platform-value">{{ number_format($staff->total_points, 2) }} poin</div>
                        </div>
                        <div class="staff-bar">
                            <span style="width: {{ $rankPercent }}%;"></span>
                        </div>
                    </div>
                @empty
                    <span class="muted">Belum ada data staff.</span>
                @endforelse
            </div>
        </div>

    </div>
@endsection
