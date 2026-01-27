@extends('layout')

@section('title', 'Follow Up Leads')

@section('content')
    <style>
        .followup-grid { display: grid; gap: 16px; }
        .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
        .calendar-day { border: 1px solid var(--border); border-radius: 10px; padding: 8px; font-size: 12px; min-height: 72px; background: #fff; }
        .calendar-day strong { display: block; font-size: 12px; margin-bottom: 4px; }
        .calendar-day .badge { font-size: 10px; }
        .reminder-list { display: grid; gap: 8px; }
    </style>

    <div class="page-header">
        <div>
            <h1>Follow Up Leads</h1>
            <p class="muted">Reminder otomatis & kalender follow-up.</p>
        </div>
    </div>

    <div class="card">
        <form class="leaderboard-filter" method="GET" action="{{ route('leads.followups') }}">
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
            <h3>Reminder Follow Up</h3>
            <div class="reminder-list">
                @forelse ($reminders as $item)
                    <div>
                        <strong>{{ $item->lead?->student_name }}</strong>
                        <div class="muted">{{ $item->follow_up_at?->format('d M Y H:i') }}</div>
                        <div class="muted">{{ $item->note }}</div>
                    </div>
                @empty
                    <div class="muted">Tidak ada reminder hari ini.</div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <h3>Kalender Follow Up ({{ $calendar['month']->format('F Y') }})</h3>
            <div class="calendar">
                @foreach ($calendar['days'] as $day)
                    <div class="calendar-day">
                        <strong>{{ $day['date']->format('d') }}</strong>
                        @if ($day['total'] > 0)
                            <span class="badge">{{ $day['total'] }} follow up</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Tambah Catatan Follow Up</h3>
        <form class="form" method="POST" action="{{ route('leads.followups.store', 0) }}" id="followup-form">
            @csrf
            <label>Leads</label>
            <select name="lead_id" required onchange="document.getElementById('followup-form').action = '{{ url('/leads') }}/' + this.value + '/followups'">
                <option value="">Pilih lead</option>
                @foreach ($leadOptions as $lead)
                    <option value="{{ $lead->id }}">{{ $lead->student_name }}</option>
                @endforeach
            </select>
            <label>Catatan</label>
            <textarea name="note" rows="3"></textarea>
            <label>Jadwal Follow Up</label>
            <input type="datetime-local" name="follow_up_at">
            <label>Status</label>
            <select name="status">
                <option value="planned">Planned</option>
                <option value="completed">Completed</option>
            </select>
            <button class="button" type="submit">Simpan Follow Up</button>
        </form>
    </div>

    <div class="card" style="margin-top: 16px; overflow-x:auto;">
        <h3>Catatan Follow Up</h3>
        <table class="table">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>Lead</th>
                <th>Catatan</th>
                <th>Status</th>
                <th>PIC</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($followups as $followup)
                <tr>
                    <td>{{ $followup->follow_up_at ? $followup->follow_up_at->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $followup->lead?->student_name ?? '-' }}</td>
                    <td>{{ $followup->note ?? '-' }}</td>
                    <td>{{ strtoupper($followup->status) }}</td>
                    <td>{{ $followup->user?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted text-center">Belum ada catatan follow-up.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination-shell">{{ $followups->links() }}</div>
    </div>
@endsection
