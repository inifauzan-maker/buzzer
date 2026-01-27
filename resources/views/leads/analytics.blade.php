@extends('layout')

@section('title', 'Analitik Leads')

@section('content')
    <style>
        .analytic-grid { display: grid; gap: 16px; }
        .stat-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .channel-bar { height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .channel-bar-fill { height: 100%; background: var(--primary); }
        .admin-table td { vertical-align: top; }
    </style>

    <div class="page-header">
        <div>
            <h1>Analitik Leads</h1>
            <p class="muted">Persentase konversi per channel, proyeksi closing, dan performa tim admin.</p>
        </div>
    </div>

    <div class="card">
        <form class="leaderboard-filter" method="GET" action="{{ route('leads.analytics') }}">
            <label class="muted">Tim</label>
            <select name="team_id" onchange="this.form.submit()">
                <option value="">Semua Tim</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) $selectedTeamId === (string) $team->id)>
                        {{ $team->team_name }}
                    </option>
                @endforeach
            </select>
            <label class="muted">Status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="prospect" @selected($selectedStatus === 'prospect')>Prospek</option>
                <option value="follow_up" @selected($selectedStatus === 'follow_up')>Follow Up</option>
                <option value="closing" @selected($selectedStatus === 'closing')>Closing</option>
                <option value="lost" @selected($selectedStatus === 'lost')>Batal</option>
            </select>
            <label class="muted">Channel</label>
            <select name="channel" onchange="this.form.submit()">
                <option value="">Semua Channel</option>
                @foreach ($channels as $channel)
                    <option value="{{ $channel }}" @selected($selectedChannel === $channel)>{{ $channel }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid" style="margin-top: 16px;">
        <div class="card">
            <h3>Proyeksi Closing</h3>
            <div class="stat-row">
                <div>
                    <div class="muted">Total Leads</div>
                    <strong>{{ $projection['total'] }}</strong>
                </div>
                <div>
                    <div class="muted">Open Leads</div>
                    <strong>{{ $projection['open'] }}</strong>
                </div>
                <div>
                    <div class="muted">Conversion Rate</div>
                    <strong>{{ $projection['rate'] }}%</strong>
                </div>
                <div>
                    <div class="muted">Proyeksi Closing</div>
                    <strong>{{ $projection['projected'] }}</strong>
                </div>
            </div>
        </div>
        <div class="card">
            <h3>Konversi per Channel</h3>
            <div class="analytic-grid">
                @foreach ($channelStats as $row)
                    @php
                        $rate = $row->total > 0 ? round(($row->closings / $row->total) * 100, 1) : 0;
                        $width = min(100, $rate);
                    @endphp
                    <div>
                        <div class="muted">{{ $row->channel ?? 'Unknown' }}</div>
                        <div class="channel-bar">
                            <div class="channel-bar-fill" style="width: {{ $width }}%;"></div>
                        </div>
                        <div class="muted">{{ $row->closings }} closing / {{ $row->total }} leads ({{ $rate }}%)</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Performa Tim Admin</h3>
        <table class="table admin-table">
            <thead>
            <tr>
                <th>Admin</th>
                <th>Total Leads</th>
                <th>Closing</th>
                <th>Konversi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($adminPerformance as $row)
                @php
                    $rate = $row->total > 0 ? round(($row->closings / $row->total) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $row->assignee?->name ?? 'Unassigned' }}</td>
                    <td>{{ $row->total }}</td>
                    <td>{{ $row->closings }}</td>
                    <td>{{ $rate }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted text-center">Belum ada data performa admin.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
