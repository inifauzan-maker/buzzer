@extends('layout')

@section('title', 'Notifikasi')

@section('content')
    <h1>Notifikasi</h1>
    <p class="muted">Daftar notifikasi terbaru.</p>

    <div class="actions" style="margin-bottom: 16px;">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="button button-outline" type="submit">Tandai semua dibaca</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Pesan</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $notification)
                    @php
                        $statusLabel = $notification->status ?? (data_get($notification, 'read_at') ? 'Dibaca' : 'Baru');
                        $statusClass = match (strtolower($statusLabel)) {
                            'dibaca' => 'verified',
                            'reviewed' => 'reviewed',
                            'pending' => 'pending',
                            default => 'pending',
                        };
                    @endphp
                    <tr>
                        <td>{{ $notification->title }}</td>
                        <td>{{ $notification->message ?? '-' }}</td>
                        <td>
                            <span class="status {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>{{ $notification->created_at?->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">Belum ada notifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $notifications->links('components.pagination') }}
    </div>
@endsection
