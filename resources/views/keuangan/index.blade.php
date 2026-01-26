@extends('layout')

@section('title', 'Keuangan')

@section('content')
    <div class="page-header">
        <div>
            <h1>Keuangan</h1>
            <p class="muted">Pemantauan pembayaran, angsuran, dan rekap keuangan siswa.</p>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Pembayaran</h3>
            <p>Kelola transaksi lunas dan angsuran siswa.</p>
        </div>
        <div class="card">
            <h3>Rekap Pembayaran</h3>
            <p>Laporan pembayaran per periode dan per siswa.</p>
        </div>
        <div class="card">
            <h3>Cetak Bukti</h3>
            <p>Unduh bukti pembayaran siswa dalam format PDF.</p>
        </div>
        <div class="card">
            <h3>Pengingat Angsuran</h3>
            <p>Kirim pengingat pembayaran sebelum jatuh tempo.</p>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        <h3>Catatan</h3>
        <p class="muted">Modul keuangan akan terhubung dengan data siswa untuk pelaporan otomatis.</p>
    </div>
@endsection
