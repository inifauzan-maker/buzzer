@extends('layout')

@section('title', 'CRM Leads')

@section('content')
    <style>
        .leads-grid { display: grid; gap: 16px; }
        .funnel { display: grid; gap: 10px; }
        .funnel-row { display: grid; grid-template-columns: 140px 1fr 60px; gap: 12px; align-items: center; }
        .funnel-bar { height: 12px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .funnel-fill { height: 100%; border-radius: inherit; background: var(--primary); }
        .funnel-fill.follow { background: var(--accent-orange); }
        .funnel-fill.close { background: var(--secondary); }
        .funnel-fill.lost { background: #94a3b8; }
        .lead-form { display: grid; gap: 12px; }
        .lead-form .grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .lead-table td { vertical-align: top; }
        .lead-status { font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
        .lead-status.prospect { color: #2563eb; }
        .lead-status.follow_up { color: #f97316; }
        .lead-status.closing { color: #c20f31; }
        .lead-status.lost { color: #64748b; }
    </style>

    <div class="page-header">
        <div>
            <h1>CRM Leads</h1>
            <p class="muted">Pipeline prospek → follow up → closing, input leads, dan ringkasan funnel.</p>
        </div>
    </div>

    <div class="card">
        <form class="leaderboard-filter" method="GET" action="{{ route('leads.index') }}">
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
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ strtoupper(str_replace('_', ' ', $status)) }}</option>
                @endforeach
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
            <h3>Funnel Prospek</h3>
            @php
                $maxFunnel = max(1, $funnel['prospect'], $funnel['follow_up'], $funnel['closing'], $funnel['lost']);
            @endphp
            <div class="funnel">
                <div class="funnel-row">
                    <span class="muted">Prospek</span>
                    <div class="funnel-bar">
                        <div class="funnel-fill" style="width: {{ ($funnel['prospect'] / $maxFunnel) * 100 }}%;"></div>
                    </div>
                    <strong>{{ $funnel['prospect'] }}</strong>
                </div>
                <div class="funnel-row">
                    <span class="muted">Follow Up</span>
                    <div class="funnel-bar">
                        <div class="funnel-fill follow" style="width: {{ ($funnel['follow_up'] / $maxFunnel) * 100 }}%;"></div>
                    </div>
                    <strong>{{ $funnel['follow_up'] }}</strong>
                </div>
                <div class="funnel-row">
                    <span class="muted">Closing</span>
                    <div class="funnel-bar">
                        <div class="funnel-fill close" style="width: {{ ($funnel['closing'] / $maxFunnel) * 100 }}%;"></div>
                    </div>
                    <strong>{{ $funnel['closing'] }}</strong>
                </div>
                <div class="funnel-row">
                    <span class="muted">Batal</span>
                    <div class="funnel-bar">
                        <div class="funnel-fill lost" style="width: {{ ($funnel['lost'] / $maxFunnel) * 100 }}%;"></div>
                    </div>
                    <strong>{{ $funnel['lost'] }}</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Input Leads</h3>
            <form class="lead-form" method="POST" action="{{ route('leads.store') }}">
                @csrf
                <div class="grid">
                    <div>
                        <label>Nama Siswa</label>
                        <input name="student_name" required value="{{ old('student_name') }}">
                    </div>
                    <div>
                        <label>Asal Sekolah</label>
                        <input name="school_name" value="{{ old('school_name') }}">
                    </div>
                    <div>
                        <label>No WA</label>
                        <input name="phone_number" value="{{ old('phone_number') }}">
                    </div>
                    <div>
                        <label>Channel</label>
                        <select name="channel">
                            <option value="">Pilih channel</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel }}">{{ $channel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Sumber</label>
                        <input name="source" placeholder="Ads/Iklan, Referensi, dsb">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ strtoupper(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Tanggal Follow Up</label>
                        <input type="datetime-local" name="follow_up_at">
                    </div>
                    <div>
                        <label>PIC/Tim Admin</label>
                        <select name="assigned_to">
                            <option value="">Pilih PIC</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ strtoupper($admin->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label>Catatan</label>
                    <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button class="button" type="submit">Simpan Lead</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top: 16px; overflow-x:auto;">
        <h3>Daftar Leads</h3>
        <table class="table lead-table">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Sekolah</th>
                <th>Channel</th>
                <th>WA</th>
                <th>Status</th>
                <th>Follow Up</th>
                <th>PIC</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($leads as $lead)
                <tr>
                    <td>{{ $lead->student_name }}</td>
                    <td>{{ $lead->school_name ?? '-' }}</td>
                    <td>{{ $lead->channel ?? '-' }}<div class="muted">{{ $lead->source }}</div></td>
                    <td>{{ $lead->phone_number ?? '-' }}</td>
                    <td class="lead-status {{ $lead->status }}">{{ strtoupper(str_replace('_', ' ', $lead->status)) }}</td>
                    <td>{{ $lead->follow_up_at ? $lead->follow_up_at->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $lead->assignee?->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('leads.status', $lead) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($lead->status === $status)>{{ strtoupper(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted text-center">Belum ada data leads.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination-shell">{{ $leads->links() }}</div>
    </div>
@endsection
