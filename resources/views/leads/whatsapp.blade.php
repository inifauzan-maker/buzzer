@extends('layout')

@section('title', 'Integrasi WhatsApp')

@section('content')
    <div class="page-header">
        <div>
            <h1>Integrasi WhatsApp (Stub)</h1>
            <p class="muted">Endpoint webhook sudah disiapkan. Tinggal hubungkan provider WhatsApp API.</p>
        </div>
    </div>

    <div class="card">
        <h3>Webhook URL</h3>
        <div class="muted">Gunakan URL berikut di provider WhatsApp:</div>
        <div style="margin-top: 8px; font-weight: 600;">{{ $webhookUrl }}</div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Catatan</h3>
        <ul>
            <li>Mode stub: saat ini tidak menyimpan data otomatis.</li>
            <li>Setelah provider dipilih, kita akan parsing payload dan membuat leads otomatis.</li>
            <li>Gunakan token/verifikasi sesuai provider (nanti ditambahkan).</li>
        </ul>
    </div>
@endsection
