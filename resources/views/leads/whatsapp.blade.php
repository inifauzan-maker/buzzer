@extends('layout')

@section('title', 'Integrasi WhatsApp')

@section('content')
    <div class="page-header">
        <div>
            <h1>Integrasi WhatsApp (WHAPI)</h1>
            <p class="muted">Mode inbound saja. Semua pesan masuk akan membuat/menambah lead.</p>
        </div>
    </div>

    <div class="card">
        <h3>Webhook URL</h3>
        <div class="muted">Gunakan URL berikut di provider WHAPI:</div>
        <div style="margin-top: 8px; font-weight: 600;">{{ $webhookUrl }}</div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Catatan</h3>
        <ul>
            <li>Gunakan token WHAPI pada header <strong>Authorization: Bearer &lt;WHAPI_TOKEN&gt;</strong>.</li>
            <li>Jika token diatur di <code>.env</code> (WHAPI_TOKEN), webhook akan memvalidasi.</li>
            <li>Inbound masuk akan dibuat sebagai lead (channel: WhatsApp, source: WHAPI).</li>
        </ul>
    </div>
@endsection
