@extends('layout')

@section('title', 'Leaderboard')

@section('content')
    <h1>Leaderboard Tim</h1>
    <p class="muted">Peringkat tim berdasarkan total poin terverifikasi.</p>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Rangking</th>
                    <th>Tim</th>
                    <th>Poin Aktivitas</th>
                    <th>Poin Konversi</th>
                    <th>Poin Staff</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaderboard as $index => $team)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td>{{ $team->team_name }}</td>
                        <td>{{ number_format($team->activity_points, 2) }}</td>
                        <td>{{ number_format($team->conversion_points, 2) }}</td>
                        <td>
                            {{ number_format($team->staff_points_total, 2) }}
                            @if ($team->top_staff_name)
                                <div class="muted">{{ $team->top_staff_name }} ({{ number_format($team->top_staff_points, 2) }})</div>
                            @endif
                        </td>
                        <td><strong>{{ number_format($team->total_points, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada data poin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
