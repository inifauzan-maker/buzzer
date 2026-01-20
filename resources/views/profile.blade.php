@extends('layout')

@section('title', 'Profil User')

@section('content')
    <h1>Profil User</h1>
    <p class="muted">Perbarui data akun Anda.</p>

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
