@extends('layout')

@section('title', 'Input Konversi')

@section('content')
    <h1>Input Konversi</h1>
    <p class="muted">Catat lead atau closing beserta bukti.</p>

    <div class="card">
        <form class="form" method="POST" action="{{ route('conversions.store') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="team_id">Tim</label>
                @if ($lockTeam)
                    @php($team = $teams->first())
                    <div>{{ $team?->team_name ?? '-' }}</div>
                    <input type="hidden" name="team_id" value="{{ $team?->id }}">
                @else
                    <select id="team_id" name="team_id" required>
                        <option value="">Pilih tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>
                                {{ $team->team_name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <label for="user_id">User</label>
                @if ($lockUser)
                    @php($selectedUser = $users->first())
                    <div>{{ $selectedUser?->name ?? '-' }}</div>
                    <input type="hidden" name="user_id" value="{{ $selectedUser?->id }}">
                @else
                    <select id="user_id" name="user_id" required>
                        <option value="">Pilih user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <label for="type">Tipe Konversi</label>
                <select id="type" name="type" required>
                    <option value="">Pilih tipe</option>
                    <option value="Lead" @selected(old('type') === 'Lead')>Lead</option>
                    <option value="Closing" @selected(old('type') === 'Closing')>Closing</option>
                </select>
            </div>
            <div>
                <label for="amount">Jumlah</label>
                <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', 1) }}" required>
            </div>
            <div>
                <label for="proof_file">Bukti (foto / pdf)</label>
                <input id="proof_file" name="proof_file" type="file" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <button class="button" type="submit">Kirim Konversi</button>
        </form>
    </div>
@endsection
