@extends('layout')

@section('title', 'Manajemen Tim')

@section('content')
    <style>
        .team-actions {
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
    </style>

    <h1>Manajemen Tim</h1>
    <p class="muted">Tambah tim baru dan lihat jumlah anggota per tim.</p>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Tambah Tim</h3>
        <form class="form" method="POST" action="{{ route('teams.store') }}">
            @csrf
            <div>
                <label for="team_name">Nama Tim</label>
                <input id="team_name" name="team_name" type="text" value="{{ old('team_name') }}" required>
            </div>
            <div>
                <label for="reminder_phone">Nomor Reminder (WhatsApp/HP)</label>
                <input id="reminder_phone" name="reminder_phone" type="text" value="{{ old('reminder_phone') }}">
            </div>
            <button class="button" type="submit">Simpan Tim</button>
        </form>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Tambah Leader / Anggota</h3>
        <form class="form" method="POST" action="{{ route('teams.members.store') }}">
            @csrf
            <div>
                <label for="team_id">Tim</label>
                <select id="team_id" name="team_id" required>
                    <option value="">Pilih tim</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>
                            {{ $team->team_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    <option value="">Pilih peran</option>
                    <option value="leader" @selected(old('role') === 'leader')>Leader</option>
                    <option value="staff" @selected(old('role') === 'staff')>Staff</option>
                </select>
            </div>
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
        <h3>Daftar Tim</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Tim</th>
                    <th>Jumlah Anggota</th>
                    <th>Leader</th>
                    <th>Anggota</th>
                    <th>Nama User</th>
                    <th>Nomor Reminder</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teams as $team)
                    @php($staffNames = $team->users->where('role', 'staff')->pluck('name')->implode(', '))
                    @php($userNames = $team->users->map(function ($user) { return $user->name . ' (' . $user->role . ')'; })->implode(', '))
                    <tr>
                        <td>{{ $team->team_name }}</td>
                        <td>{{ $team->users_count }}</td>
                        <td>{{ $team->leader?->name ?? '-' }}</td>
                        <td>
                            {{ $team->users->where('role', 'staff')->count() }} staff
                            @if ($staffNames)
                                <div class="muted">{{ $staffNames }}</div>
                            @endif
                        </td>
                        <td>{{ $userNames ?: '-' }}</td>
                        <td>{{ $team->reminder_phone ?? '-' }}</td>
                        <td>
                            <div class="team-actions">
                                <details class="inline-edit">
                                    <summary class="button button-outline">Edit</summary>
                                    <form class="inline-form" method="POST" action="{{ route('teams.update', $team) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label>Nama Tim</label>
                                            <input name="team_name" type="text" value="{{ old('team_name', $team->team_name) }}" required>
                                        </div>
                                        <div>
                                            <label>Nomor Reminder</label>
                                            <input name="reminder_phone" type="text" value="{{ old('reminder_phone', $team->reminder_phone) }}">
                                        </div>
                                        <button class="button" type="submit">Simpan</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Hapus tim ini? (hanya boleh jika tidak ada data terkait)');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-outline" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">Belum ada tim.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
