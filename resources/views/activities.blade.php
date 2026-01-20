@extends('layout')

@section('title', 'Aktivitas Konten')

@section('content')
    <h1>Aktivitas Konten</h1>
    <p class="muted">Input dan verifikasi aktivitas konten harian.</p>

    <div class="actions" style="margin-bottom: 16px;">
        <a class="button" href="{{ route('activities.create') }}">Input Aktivitas</a>
    </div>

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
                            Like {{ $activity->likes }},
                            Comm {{ $activity->comments }},
                            Share {{ $activity->shares }},
                            Save {{ $activity->saves }},
                            Reach {{ $activity->reach }}
                        </td>
                        <td>
                            <span class="status {{ strtolower($activity->status) }}">{{ $activity->status }}</span>
                            <div class="muted">Grade {{ $activity->admin_grade }}</div>
                        </td>
                        <td>{{ $activity->computed_points ? number_format($activity->computed_points, 2) : '-' }}</td>
                        <td>
                            @if ($activity->evidence_screenshot)
                                <a class="button button-outline" target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($activity->evidence_screenshot) }}">Bukti</a>
                            @endif
                            @if ($activity->status === 'Pending' && auth()->user()->role !== 'staff')
                                <form method="POST" action="{{ route('activities.verify', $activity) }}" style="margin-top: 8px;">
                                    @csrf
                                    <div class="actions">
                                        <select name="admin_grade" required>
                                            <option value="A">Grade A</option>
                                            <option value="B" selected>Grade B</option>
                                            <option value="C">Grade C</option>
                                        </select>
                                        <button class="button" type="submit">Verify</button>
                                    </div>
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
