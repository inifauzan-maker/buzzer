@extends('layout')

@section('title', 'Manajemen User')

@section('content')
    <style>
        .user-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
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
        .user-badge {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e6f7fa;
            color: #0b5f57;
            display: inline-block;
        }
    </style>

    <h1>Manajemen User</h1>
    <p class="muted">Kelola akun superadmin, leader, dan staff.</p>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Tambah User</h3>
        <form class="form" method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="grid">
                <div>
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label for="phone">No. HP / WA</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="grid">
                <div>
                    <label for="role">Peran</label>
                    <select id="role" name="role" required>
                        <option value="">Pilih peran</option>
                        <option value="superadmin" @selected(old('role') === 'superadmin')>Superadmin</option>
                        <option value="leader" @selected(old('role') === 'leader')>Leader</option>
                        <option value="staff" @selected(old('role') === 'staff')>Staff</option>
                        <option value="guest" @selected(old('role') === 'guest')>Guest</option>
                    </select>
                </div>
                <div>
                    <label for="team_id">Tim (wajib untuk leader/staff)</label>
                    <select id="team_id" name="team_id">
                        <option value="">-</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->team_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid">
                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>
                <div>
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
            </div>
            <button class="button" type="submit">Simpan User</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar User</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tim</th>
                    <th>No. HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="user-badge">{{ strtoupper($user->role) }}</span></td>
                        <td>{{ $user->team?->team_name ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <div class="user-actions">
                                <details class="inline-edit">
                                    <summary class="button button-outline">Edit</summary>
                                    <form class="inline-form" method="POST" action="{{ route('users.update', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid">
                                            <div>
                                                <label>Nama</label>
                                                <input name="name" type="text" value="{{ old('name', $user->name) }}" required>
                                            </div>
                                            <div>
                                                <label>Email</label>
                                                <input name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                            </div>
                                            <div>
                                                <label>No. HP</label>
                                                <input name="phone" type="text" value="{{ old('phone', $user->phone) }}">
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div>
                                                <label>Peran</label>
                                                <select name="role" required>
                                                    <option value="superadmin" @selected(old('role', $user->role) === 'superadmin')>Superadmin</option>
                                                    <option value="leader" @selected(old('role', $user->role) === 'leader')>Leader</option>
                                                    <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                                                    <option value="guest" @selected(old('role', $user->role) === 'guest')>Guest</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label>Tim (wajib untuk leader/staff)</label>
                                                <select name="team_id">
                                                    <option value="">-</option>
                                                    @foreach ($teams as $team)
                                                        <option value="{{ $team->id }}" @selected((int) old('team_id', $user->team_id) === $team->id)>
                                                            {{ $team->team_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div>
                                                <label>Password Baru</label>
                                                <input name="password" type="password">
                                            </div>
                                            <div>
                                                <label>Konfirmasi Password</label>
                                                <input name="password_confirmation" type="password">
                                            </div>
                                        </div>
                                        <button class="button" type="submit">Simpan</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini? Aktivitas/konversi user akan ikut terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-outline" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
