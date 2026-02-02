@extends('layout')

@section('title', 'Pengaturan Maintenance')

@section('content')
    <h1>Pengaturan Maintenance</h1>
    <p class="muted">Aktifkan mode maintenance untuk membatasi akses aplikasi hanya untuk admin.</p>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.maintenance.update') }}">
            @csrf
            <div style="display: grid; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="enabled" value="1" @checked($maintenanceEnabled)>
                    Aktifkan maintenance (hanya admin yang bisa akses)
                </label>
                <div>
                    <label for="message">Pesan untuk pengguna</label>
                    <textarea id="message" name="message" rows="3" placeholder="Contoh: Sistem sedang dalam pemeliharaan.">
{{ old('message', $maintenanceMessage) }}</textarea>
                </div>
                <button class="button" type="submit">Simpan</button>
            </div>
        </form>
    </div>
@endsection
