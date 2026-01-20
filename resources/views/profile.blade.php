@extends('layout')

@section('title', 'Profil User')

@section('content')
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .split-card {
            display: grid;
            gap: 10px;
        }
        .split-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        .chip {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e6f7fa;
            color: #0b5f57;
        }
        .pie-wrap {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 16px;
            align-items: center;
        }
        .pie {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e5e7eb;
        }
        .pie-center {
            width: 96px;
            height: 96px;
            background: #fff;
            border-radius: 50%;
            display: grid;
            place-items: center;
            text-align: center;
            box-shadow: inset 0 0 0 1px #e5e7eb;
            font-size: 12px;
        }
        .legend {
            display: grid;
            gap: 8px;
            font-size: 13px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .legend-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
    </style>

    <h1>Profil User</h1>
    <p class="muted">Perbarui data akun Anda.</p>

    @if ($user->role === 'staff')
        <div class="profile-grid">
            <div class="card">
                <h3>Visual Poin per Platform</h3>
                @php
                    $totalPlatformPoints = $platformPoints->sum('total_points');
                    $colors = ['#12b5c9', '#3f475d', '#f97316', '#22c55e', '#ef4444', '#8b5cf6'];
                    $segments = [];
                    $current = 0;
                    foreach ($platformPoints as $index => $row) {
                        $percent = $totalPlatformPoints > 0 ? ($row->total_points / $totalPlatformPoints) * 100 : 0;
                        $end = $current + $percent;
                        $color = $colors[$index % count($colors)];
                        $segments[] = $color.' '.$current.'% '.$end.'%';
                        $current = $end;
                    }
                    $pieBackground = $segments
                        ? 'conic-gradient('.implode(', ', $segments).')'
                        : 'conic-gradient(#e5e7eb 0 100%)';
                @endphp
                <div class="pie-wrap">
                    <div class="pie" style="background: {{ $pieBackground }};">
                        <div class="pie-center">
                            <strong>{{ number_format($totalPlatformPoints, 2) }}</strong>
                            <span class="muted">Total</span>
                        </div>
                    </div>
                    <div class="legend">
                        @forelse ($platformPoints as $index => $row)
                            @php($percent = $totalPlatformPoints > 0 ? round(($row->total_points / $totalPlatformPoints) * 100) : 0)
                            <div class="legend-item">
                                <div class="legend-left">
                                    <span class="legend-dot" style="background: {{ $colors[$index % count($colors)] }};"></span>
                                    <span>{{ $row->platform }}</span>
                                </div>
                                <span>{{ number_format($row->total_points, 2) }} ({{ $percent }}%)</span>
                            </div>
                        @empty
                            <span class="muted">Belum ada poin platform.</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Aktivitas vs Konversi</h3>
                <div class="split-card">
                    <div class="split-row">
                        <span>Poin Aktivitas</span>
                        <span class="chip">{{ number_format($activityPoints, 2) }}</span>
                    </div>
                    <div class="split-row">
                        <span>Poin Konversi</span>
                        <span class="chip">{{ number_format($conversionPoints, 2) }}</span>
                    </div>
                    <div class="split-row">
                        <strong>Total</strong>
                        <strong>{{ number_format($activityPoints + $conversionPoints, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <form class="form" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label for="phone">No. HP / WA</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="grid">
                <div>
                    <label for="password">Password Baru</label>
                    <input id="password" name="password" type="password">
                </div>
                <div>
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password">
                </div>
            </div>
            <button class="button" type="submit">Simpan Profil</button>
        </form>
    </div>
@endsection
