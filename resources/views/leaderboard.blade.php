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
            gap: 16px;
        }
        .staff-chart-legend {
            display: flex;
            gap: 14px;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
            justify-content: center;
        }
        .legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .legend-activity { background: var(--primary); }
        .legend-conversion { background: var(--secondary); }
        .staff-chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 18px;
            align-items: end;
            padding: 14px 16px 18px;
            border-radius: 18px;
            background:
                linear-gradient(#f6f8fb, #f6f8fb),
                repeating-linear-gradient(
                    to top,
                    rgba(15, 23, 42, 0.08) 0px,
                    rgba(15, 23, 42, 0.08) 1px,
                    transparent 1px,
                    transparent 28px
                );
            border: 1px solid var(--border);
        }
        .staff-group {
            display: grid;
            gap: 8px;
            justify-items: center;
        }
        .bar-wrap {
            height: 200px;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 10px;
            padding: 6px 4px 0;
        }
        .bar {
            width: 46%;
            border-radius: 12px 12px 8px 8px;
            position: relative;
            min-height: 8px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
        }
        .bar.activity { background: var(--primary); }
        .bar.conversion { background: var(--secondary); }
        .bar-value {
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            white-space: nowrap;
        }
        .bar-name {
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }
        @media (max-width: 820px) {
            .staff-chart-grid { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
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

        <div class="staff-chart-legend" style="margin-bottom: 12px;">
            <span><span class="legend-dot legend-activity"></span> Aktivitas</span>
            <span><span class="legend-dot legend-conversion"></span> Konversi</span>
        </div>

        <div class="staff-chart">
            <div class="staff-chart-grid">
                @forelse ($leaderboardStaff as $user)
                    @php
                        $activity = (float) $user->activity_points;
                        $conversion = (float) $user->conversion_points;
                        $activityHeight = $maxStaffPoint > 0 ? ($activity / $maxStaffPoint) * 100 : 0;
                        $conversionHeight = $maxStaffPoint > 0 ? ($conversion / $maxStaffPoint) * 100 : 0;
                    @endphp
                    <div class="staff-group">
                        <div class="bar-wrap">
                            <div class="bar activity" style="height: {{ $activityHeight }}%;">
                                <span class="bar-value">{{ number_format($activity, 2) }}</span>
                            </div>
                            <div class="bar conversion" style="height: {{ $conversionHeight }}%;">
                                <span class="bar-value">{{ number_format($conversion, 2) }}</span>
                            </div>
                        </div>
                        <div class="bar-name">
                            <a href="{{ route('profile.view', $user->id) }}" title="Lihat profil {{ $user->name }}">
                                {{ $user->name }}
                            </a>
                        </div>
                        <div class="muted" style="font-size: 11px;">{{ $user->team_name ?? '-' }}</div>
                    </div>
                @empty
                    <div class="muted">Belum ada data staff.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
