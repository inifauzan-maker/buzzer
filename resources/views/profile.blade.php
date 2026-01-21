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
        .target-metrics {
            display: grid;
            gap: 6px;
            font-size: 13px;
        }
        .bar-cell {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }
        .bar-chart {
            position: relative;
            height: 110px;
            padding: 10px 8px 14px 12px;
            display: grid;
            grid-template-rows: 1fr auto;
            gap: 6px;
            background: repeating-linear-gradient(
                to top,
                rgba(148, 163, 184, 0.25) 0,
                rgba(148, 163, 184, 0.25) 1px,
                transparent 1px,
                transparent 28px
            );
            border-left: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            border-radius: 8px;
        }
        .bar-stack {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 70px;
            justify-content: center;
        }
        .bar {
            width: 22px;
            border-radius: 6px 6px 4px 4px;
            background: #94a3b8;
        }
        .bar.achieved { background: #12b5c9; }
        .bar.target { background: #93c5fd; }
        .bar.leads { background: #f97316; }
        .bar-values {
            display: flex;
            justify-content: center;
            gap: 8px;
            font-size: 11px;
            color: var(--muted);
        }
        .social-table td, .social-table th {
            font-size: 13px;
        }
        .social-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        details.inline-edit summary {
            list-style: none;
            cursor: pointer;
        }
        details.inline-edit summary::-webkit-details-marker {
            display: none;
        }
        details.inline-edit[open] summary {
            margin-bottom: 8px;
        }
        .inline-form {
            display: grid;
            gap: 8px;
        }
    </style>

    <h1>Profil User</h1>
    <p class="muted">Perbarui data akun Anda.</p>

    @includeWhen($user->role === 'staff', 'profile-staff')

    <div class="card" style="margin-bottom: 20px;">
        <h3>Akun Media Sosial</h3>
        @if (!empty($isOwner))
            <form class="form" method="POST" action="{{ route('profile.social.store') }}">
                @csrf
                <div class="grid">
                    <div>
                        <label for="platform">Platform</label>
                        <select id="platform" name="platform" required>
                            <option value="">Pilih platform</option>
                            @foreach ($platformOptions as $platform)
                                <option value="{{ $platform }}" @selected(old('platform') === $platform)>{{ $platform }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="handle">Username / Handle</label>
                        <input id="handle" name="handle" type="text" value="{{ old('handle') }}" required>
                    </div>
                    <div>
                        <label for="profile_url">Link Profil</label>
                        <input id="profile_url" name="profile_url" type="text" value="{{ old('profile_url') }}">
                    </div>
                </div>
                <div class="grid">
                    <div>
                        <label for="followers">Followers</label>
                        <input id="followers" name="followers" type="number" min="0" value="{{ old('followers') }}">
                    </div>
                    <div>
                        <label for="following">Following</label>
                        <input id="following" name="following" type="number" min="0" value="{{ old('following') }}">
                    </div>
                    <div>
                        <label for="posts_count">Posts</label>
                        <input id="posts_count" name="posts_count" type="number" min="0" value="{{ old('posts_count') }}">
                    </div>
                </div>
                <div>
                    <label for="notes">Catatan</label>
                    <input id="notes" name="notes" type="text" value="{{ old('notes') }}">
                </div>
                <button class="button" type="submit">Tambah Akun</button>
            </form>
        @endif

        <div style="margin-top: 16px;">
            <table class="social-table">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Handle</th>
                        <th>Followers</th>
                        <th>Following</th>
                        <th>Posts</th>
                        <th>Link</th>
                        <th>Catatan</th>
                        @if (!empty($isOwner))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($socialAccounts as $account)
                        <tr>
                            <td>{{ $account->platform }}</td>
                            <td>{{ $account->handle }}</td>
                            <td>{{ $account->followers ?? '-' }}</td>
                            <td>{{ $account->following ?? '-' }}</td>
                            <td>{{ $account->posts_count ?? '-' }}</td>
                            <td>
                                @if ($account->profile_url)
                                    <a href="{{ $account->profile_url }}" target="_blank" rel="noopener">Buka</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $account->notes ?? '-' }}</td>
                            @if (!empty($isOwner))
                                <td>
                                    <div class="social-actions">
                                        <details class="inline-edit">
                                            <summary class="button button-outline">Edit</summary>
                                            <form class="inline-form" method="POST" action="{{ route('profile.social.update', $account) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="grid">
                                                    <div>
                                                        <label>Platform</label>
                                                        <select name="platform" required>
                                                            @foreach ($platformOptions as $platform)
                                                                <option value="{{ $platform }}" @selected($account->platform === $platform)>{{ $platform }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label>Handle</label>
                                                        <input name="handle" type="text" value="{{ $account->handle }}" required>
                                                    </div>
                                                    <div>
                                                        <label>Link Profil</label>
                                                        <input name="profile_url" type="text" value="{{ $account->profile_url }}">
                                                    </div>
                                                </div>
                                                <div class="grid">
                                                    <div>
                                                        <label>Followers</label>
                                                        <input name="followers" type="number" min="0" value="{{ $account->followers }}">
                                                    </div>
                                                    <div>
                                                        <label>Following</label>
                                                        <input name="following" type="number" min="0" value="{{ $account->following }}">
                                                    </div>
                                                    <div>
                                                        <label>Posts</label>
                                                        <input name="posts_count" type="number" min="0" value="{{ $account->posts_count }}">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label>Catatan</label>
                                                    <input name="notes" type="text" value="{{ $account->notes }}">
                                                </div>
                                                <button class="button" type="submit">Simpan</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ route('profile.social.destroy', $account) }}" onsubmit="return confirm('Hapus akun media sosial ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button button-outline" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($isOwner) ? 8 : 7 }}" class="muted">Belum ada akun media sosial.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (!empty($isOwner))
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
    @else
        <div class="card">
            <h3>Info User</h3>
            <div class="split-card">
                <div class="split-row">
                    <span>Nama</span>
                    <strong>{{ $user->name }}</strong>
                </div>
                <div class="split-row">
                    <span>Email</span>
                    <strong>{{ $user->email }}</strong>
                </div>
                <div class="split-row">
                    <span>Role</span>
                    <strong>{{ strtoupper($user->role) }}</strong>
                </div>
                <div class="split-row">
                    <span>Tim</span>
                    <strong>{{ $user->team?->team_name ?? '-' }}</strong>
                </div>
                <div class="split-row">
                    <span>No. HP</span>
                    <strong>{{ $user->phone ?? '-' }}</strong>
                </div>
            </div>
        </div>
    @endif
@endsection
