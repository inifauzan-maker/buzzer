@extends('layout')

@section('title', 'Edit Aktivitas')

@section('content')
    <h1>Edit Aktivitas</h1>
    <p class="muted">Perbarui data engagement. Status akan direset menjadi Pending setelah disimpan.</p>

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card">
        <form class="form" method="POST" action="{{ route('activities.update', $activity) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="form-grid">
                <label class="input-field">
                    Tim
                    <input type="text" value="{{ $activity->team?->team_name ?? '-' }}" disabled>
                </label>
                <label class="input-field">
                    User
                    <input type="text" value="{{ $activity->user?->name ?? '-' }}" disabled>
                </label>
                <label class="input-field">
                    Platform
                    <input type="text" value="{{ $activity->platform }}" disabled>
                </label>
                <label class="input-field">
                    Link
                    <a class="button button-outline" target="_blank" rel="noopener" href="{{ $activity->post_url }}">Buka</a>
                </label>

                <label class="input-field">
                    Like
                    <input type="number" name="likes" value="{{ old('likes', $activity->likes) }}" min="0">
                </label>
                <label class="input-field">
                    Komentar
                    <input type="number" name="comments" value="{{ old('comments', $activity->comments) }}" min="0">
                </label>
                <label class="input-field">
                    Share
                    <input type="number" name="shares" value="{{ old('shares', $activity->shares) }}" min="0">
                </label>
                <label class="input-field">
                    Save
                    <input type="number" name="saves" value="{{ old('saves', $activity->saves) }}" min="0">
                </label>
                <label class="input-field">
                    Reach
                    <input type="number" name="reach" value="{{ old('reach', $activity->reach) }}" min="0">
                </label>
                <label class="input-field">
                    Bukti (opsional, ganti jika perlu)
                    <input type="file" name="evidence_screenshot" accept="image/*">
                    @if ($activity->evidence_screenshot)
                        <small class="muted">
                            <a target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($activity->evidence_screenshot) }}">Lihat bukti lama</a>
                        </small>
                    @endif
                </label>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Simpan Perubahan</button>
                <a class="button button-outline" href="{{ route('activities.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
