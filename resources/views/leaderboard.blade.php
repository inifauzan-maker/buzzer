@extends('layout')

@section('title', 'Leaderboard')

@section('content')
    <h1>Leaderboard</h1>
    <p class="muted">Peringkat berdasarkan perolehan poin leader dan staff.</p>

    <div class="card" style="margin-bottom: 18px;">
        <h2>Leaderboard Leader</h2>
        <table>
            <thead>
                <tr>
                    <th>Rangking</th>
                    <th>Nama</th>
                    <th>Tim</th>
                    <th>Poin Konversi</th>
                    <th>Poin Aktivitas</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaderboardLeaders as $index => $user)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ route('profile.view', $user->id) }}"
                               title="Lihat profil {{ $user->name }}">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td>{{ $user->team_name ?? '-' }}</td>
                        <td>{{ number_format($user->conversion_points, 2) }}</td>
                        <td>{{ number_format($user->activity_points, 2) }}</td>
                        <td><strong>{{ number_format($user->total_points, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada data leader.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Leaderboard Staff</h2>
        <table>
            <thead>
                <tr>
                    <th>Rangking</th>
                    <th>Nama</th>
                    <th>Tim</th>
                    <th>Poin Konversi</th>
                    <th>Poin Aktivitas</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaderboardStaff as $index => $user)
                    <tr>
                        <td>#{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ route('profile.view', $user->id) }}"
                               title="Lihat profil {{ $user->name }}">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td>{{ $user->team_name ?? '-' }}</td>
                        <td>{{ number_format($user->conversion_points, 2) }}</td>
                        <td>{{ number_format($user->activity_points, 2) }}</td>
                        <td><strong>{{ number_format($user->total_points, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada data staff.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
