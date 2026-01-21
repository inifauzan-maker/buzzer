@extends('layout')

@section('title', 'Aktivitas Konten')

@section('content')
    <h1>Aktivitas Konten</h1>
    <p class="muted">Input dan verifikasi aktivitas konten harian.</p>

    @if (auth()->user()->role !== 'guest')
        <div class="actions" style="margin-bottom: 16px;">
            <a class="button" href="{{ route('activities.create') }}">Input Aktivitas</a>
        </div>
    @endif

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tim / User</th>
                    <th>Platform</th>
                    <th>Link</th>
                    <th>Engagement</th>
                    <th>Status</th>
                    <th>Poin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>{{ $activity->post_date?->format('d M Y') }}</td>
                        <td>
                            {{ $activity->team?->team_name ?? '-' }}
                            <div class="muted">{{ $activity->user?->name ?? '-' }}</div>
                        </td>
                        <td>{{ $activity->platform }}</td>
                        <td>
                            <a class="button button-outline" target="_blank" rel="noopener" href="{{ $activity->post_url }}">Buka</a>
                        </td>
                        <td>
                            <span title="Rumus ER: (Like+Comm+Save+Share) / Reach. Bobot: <1% (0), >=1% (10), >=3% (30), >=6% (50).">
                                Like {{ $activity->likes }},
                                Comm {{ $activity->comments }},
                                Share {{ $activity->shares }},
                                Save {{ $activity->saves }},
                                Reach {{ $activity->reach }}
                            </span>
                        </td>
                        <td>
                            <span class="status {{ strtolower($activity->status) }}">{{ $activity->status }}</span>
                        </td>
                        <td>{{ $activity->computed_points ? number_format($activity->computed_points, 2) : '-' }}</td>
                        <td>
                            @if ($activity->evidence_screenshot)
                                <a class="button button-outline" target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($activity->evidence_screenshot) }}">Bukti</a>
                            @endif
                            @if ($activity->status === 'Pending' && auth()->user()->role === 'leader')
                                <form method="POST" action="{{ route('activities.verify', $activity) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button" type="submit">Review</button>
                                </form>
                                <form method="POST" action="{{ route('activities.reject', $activity) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button button-outline" type="submit">Reject</button>
                                </form>
                            @endif
                            @if ($activity->status === 'Reviewed' && auth()->user()->role === 'superadmin')
                                <form method="POST" action="{{ route('activities.verify', $activity) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button" type="submit">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('activities.reject', $activity) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button button-outline" type="submit">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 12px;">
            {{ $activities->links() }}
        </div>
    </div>
@endsection
