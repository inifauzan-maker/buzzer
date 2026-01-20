@extends('layout')

@section('title', 'Input Aktivitas')

@section('content')
    <h1>Input Aktivitas</h1>
    <p class="muted">Masukkan data konten harian tim.</p>

    <div class="card">
        <form class="form" method="POST" action="{{ route('activities.store') }}" enctype="multipart/form-data">
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
                <label for="platform">Platform</label>
                <select id="platform" name="platform" required>
                    <option value="">Pilih platform</option>
                    @foreach ($platforms as $platform)
                        <option value="{{ $platform }}" @selected(old('platform') == $platform)>
                            {{ $platform }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="post_url">Link Postingan</label>
                <input id="post_url" name="post_url" type="text" value="{{ old('post_url') }}" required>
            </div>
            <div>
                <label for="post_date">Tanggal Posting</label>
                <input id="post_date" name="post_date" type="date" value="{{ old('post_date', now()->toDateString()) }}" required>
            </div>
            <div class="grid">
                <div>
                    <label for="likes">Likes</label>
                    <input id="likes" name="likes" type="number" min="0" value="{{ old('likes', 0) }}">
                </div>
                <div>
                    <label for="comments">Comments</label>
                    <input id="comments" name="comments" type="number" min="0" value="{{ old('comments', 0) }}">
                </div>
                <div>
                    <label for="shares">Shares</label>
                    <input id="shares" name="shares" type="number" min="0" value="{{ old('shares', 0) }}">
                </div>
                <div>
                    <label for="saves">Saves</label>
                    <input id="saves" name="saves" type="number" min="0" value="{{ old('saves', 0) }}">
                </div>
                <div>
                    <label for="reach">Reach</label>
                    <input id="reach" name="reach" type="number" min="0" value="{{ old('reach', 0) }}">
                </div>
            </div>
            <div>
                <label for="evidence_screenshot">Bukti Screenshot</label>
                <input id="evidence_screenshot" name="evidence_screenshot" type="file" accept="image/*">
            </div>
            <button class="button" type="submit">Kirim Aktivitas</button>
        </form>
    </div>
@endsection
