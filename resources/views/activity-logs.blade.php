@extends('layout')

@section('title', 'Log Aktivitas')

@section('content')
    <style>
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .log-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 700;
        }
        .log-icon {
            width: 22px;
            height: 22px;
            display: inline-block;
        }
        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .log-table th {
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .log-table th,
        .log-table td {
            border: 1px solid #dbe4f3;
            padding: 8px 10px;
        }
        .log-table td.role {
            color: #dc2626;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
        }
        .log-table td.activity {
            font-style: italic;
        }
        .log-scroll {
            max-height: 520px;
            overflow: auto;
            border-radius: 12px;
            border: 1px solid #dbe4f3;
        }
        .log-button {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }
        .log-button:hover {
            background: #dc2626;
        }
    </style>

    <div class="log-header">
        <div class="log-title">
            <svg class="log-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#2563eb" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 5h-2v6l4 2 1-1.73-3-1.52V7Z"/>
            </svg>
            <span>Log Activity</span>
        </div>
        <form method="POST" action="{{ route('activity-logs.clear') }}" onsubmit="return confirm('Bersihkan semua log aktivitas?');">
            @csrf
            <button class="log-button" type="submit">Bersihkan Log</button>
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <div class="log-scroll">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama User</th>
                        <th>Role</th>
                        <th>Aktivitas</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Jam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $loop->index }}</td>
                            <td>{{ $log->user_name ?? '-' }}</td>
                            <td class="role">{{ $log->role ? strtoupper($log->role) : '-' }}</td>
                            <td class="activity">{{ $log->activity }}</td>
                            <td>{{ $log->created_at->format('d/m/Y') }}</td>
                            <td>{{ $log->created_at->locale('id')->translatedFormat('l') }}</td>
                            <td>{{ $log->created_at->format('H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 16px;">
        {{ $logs->links() }}
    </div>
@endsection
