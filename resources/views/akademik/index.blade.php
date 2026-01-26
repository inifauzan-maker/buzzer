@extends('layout')

@section('title', 'Akademik')

@section('content')
    <div class="page-header">
        <div>
            <h1>Akademik</h1>
            <p class="muted">Ringkasan pengelolaan kelas, absensi, dan kegiatan belajar.</p>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Data Kelas</h3>
            <p>Kelola daftar kelas dan jadwal pembelajaran.</p>
        </div>
        <div class="card">
            <h3>Absensi Siswa</h3>
            <p>Catat kehadiran harian dan rekap absensi.</p>
        </div>
        <div class="card">
            <h3>Kegiatan Belajar</h3>
            <p>Daftar kegiatan belajar dan dokumentasi kelas.</p>
        </div>
        <div class="card">
            <h3>Laporan Kemajuan</h3>
            <p>Ringkasan progres dan karya siswa.</p>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        <h3>Catatan</h3>
        <p class="muted">Modul akademik akan dilengkapi dengan tabel data dan fitur cetak laporan.</p>
    </div>
@endsection
