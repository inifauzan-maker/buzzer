@extends('layout')

@section('title', 'Data Siswa')

@section('content')
    <div class="page-header">
        <div>
            <h1>Data Siswa</h1>
            <p class="muted">Daftar pendaftar dari form publik.</p>
        </div>
    </div>

    <div class="card" style="overflow-x:auto;">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Asal Sekolah</th>
                <th>Kelas</th>
                <th>Program</th>
                <th>Lokasi</th>
                <th>Kota / Provinsi</th>
                <th>Telepon</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($registrations as $index => $reg)
                <tr>
                    <td>{{ $registrations->firstItem() + $index }}</td>
                    <td>{{ optional($reg->created_at)->format('d M Y H:i') }}</td>
                    <td>{{ $reg->full_name ?? $reg->name }}</td>
                    <td>{{ $reg->school_name ?? '-' }}</td>
                    <td>{{ $reg->class_level ?? '-' }}</td>
                    <td>{{ $reg->program ?? '-' }}</td>
                    <td>{{ $reg->study_location ?? '-' }}</td>
                    <td>{{ trim(($reg->city ?? '').' / '.($reg->province ?? ''), ' /') }}</td>
                    <td>{{ $reg->phone_number ?? $reg->phone }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center muted">Belum ada data pendaftaran.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-shell">
        {{ $registrations->links() }}
    </div>
@endsection
