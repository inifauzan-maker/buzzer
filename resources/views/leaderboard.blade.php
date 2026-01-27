@extends('layout')

@section('title', 'Leaderboard')

@section('content')
    <h1>Leaderboard</h1>
    <p class="muted">Perolehan poin aktivitas dan konversi staff per tim.</p>

    <style>
        .leaderboard-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin: 16px 0 20px;
        }
        .leaderboard-filter select {
            min-width: 200px;
        }
        .staff-chart {
            display: grid;
            gap: 14px;
        }
        .staff-chart-legend {
            display: flex;
            gap: 14px;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
        }
        .legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .legend-activity { background: #2563eb; }
        .legend-conversion { background: #f97316; }
        .staff-bar-group {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 16px;
            align-items: end;
        }
        .staff-bar-name {
            font-size: 13px;
            color: var(--muted);
        }
        .staff-bar-name strong { display: block; color: var(--ink); }
        .staff-bar-columns {
            display: flex;
            align-items: flex-end;
            gap: 16px;
            padding: 12px 12px 6px;
            border-radius: 14px;
            background: #f6f8fb;
        }
        .bar-stack {
            flex: 1;
            display: grid;
            gap: 6px;
        }
        .bar-wrap {
            height: 140px;
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }
        .bar {
            flex: 1;
            border-radius: 10px 10px 6px 6px;
            background: #e2e8f0;
            position: relative;
            min-width: 30px;
        }
        .bar.activity { background: #2563eb; }
        .bar.conversion { background: #f97316; }
        .bar-value {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            font-weight: 600;
            color: #3f475d;
            white-space: nowrap;
        }
        .bar-label {
            text-align: center;
            font-size: 11px;
            color: var(--muted);
        }
        @media (max-width: 820px) {
            .staff-bar-group { grid-template-columns: 1fr; }
        }
    </style>

    <div class="card">
        <h2>Diagram Peraihan Staff</h2>
        <form class="leaderboard-filter" method="GET" action="{{ route('leaderboard') }}">
            <label class="muted">Filter Tim</label>
            <select name="team_id" onchange="this.form.submit()">
                <option value="">Semua Tim</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) $selectedTeamId === (string) $team->id)>
                        {{ $team->team_name }}
                    </option>
                @endforeach
            </select>
            <label class="muted">Urutkan</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="total" @selected(($selectedSort ?? 'total') === 'total')>Total</option>
                <option value="activity" @selected(($selectedSort ?? '') === 'activity')>Aktivitas</option>
                <option value="conversion" @selected(($selectedSort ?? '') === 'conversion')>Konversi</option>
            </select>
        </form>

        @php
            $maxStaffPoint = max(
                1,
                (int) ($leaderboardStaff->max('activity_points') ?? 0),
                (int) ($leaderboardStaff->max('conversion_points') ?? 0)
            );
        @endphp

        <div class="staff-chart-legend" style="margin-bottom: 8px;">
            <span><span class="legend-dot legend-activity"></span> Aktivitas</span>
            <span><span class="legend-dot legend-conversion"></span> Konversi</span>
        </div>

        <div class="staff-chart">
            @forelse ($leaderboardStaff as $user)
                @php
                    $activity = (float) $user->activity_points;
                    $conversion = (float) $user->conversion_points;
                    $activityHeight = $maxStaffPoint > 0 ? ($activity / $maxStaffPoint) * 100 : 0;
                    $conversionHeight = $maxStaffPoint > 0 ? ($conversion / $maxStaffPoint) * 100 : 0;
                @endphp
                <div class="staff-bar-group">
                    <div class="staff-bar-name">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->team_name ?? '-' }}</span>
                    </div>
                    <div class="staff-bar-columns">
                        <div class="bar-stack">
                            <div class="bar-wrap">
                                <div class="bar activity" style="height: {{ $activityHeight }}%;">
                                    <span class="bar-value">{{ number_format($activity, 2) }}</span>
                                </div>
                                <div class="bar conversion" style="height: {{ $conversionHeight }}%;">
                                    <span class="bar-value">{{ number_format($conversion, 2) }}</span>
                                </div>
                            </div>
                            <div class="bar-label">Aktivitas vs Konversi</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="muted">Belum ada data staff.</div>
            @endforelse
        </div>
    </div>
@endsection
