<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invoice {{ $invoiceNumber }}</title>
        <style>
            :root {
                --primary: #0a0a5c;
                --secondary: #c20f31;
                --accent: #ff7e24;
                --ink: #1f2937;
                --muted: #6b7280;
                --border: #e5e7eb;
                --paper: #ffffff;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                color: var(--ink);
                background: #f3f4f6;
                padding: 24px;
            }
            .page {
                max-width: 820px;
                margin: 0 auto;
                background: var(--paper);
                border: 1px solid var(--border);
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            }
            .header {
                display: grid;
                grid-template-columns: 1fr 240px;
                gap: 18px;
                padding: 28px 32px 20px;
                align-items: center;
            }
            .brand {
                display: grid;
                gap: 6px;
            }
            .logo-badge {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: var(--secondary);
                display: grid;
                place-items: center;
                color: #fff;
                font-weight: 700;
                letter-spacing: 0.08em;
            }
            .brand small {
                color: var(--muted);
            }
            .invoice-title {
                background: var(--secondary);
                color: #fff;
                text-align: center;
                padding: 10px 12px;
                font-weight: 700;
                letter-spacing: 0.3em;
            }
            .badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                background: var(--secondary);
                color: #fff;
            }
            .badge.accent {
                background: var(--accent);
            }
            .meta {
                padding: 0 32px 18px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 18px;
            }
            .meta h3 {
                margin: 0 0 8px;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--secondary);
            }
            .meta p {
                margin: 4px 0;
                font-size: 14px;
            }
            .tagline {
                font-weight: 700;
            }
            .section {
                padding: 0 32px 20px;
            }
            .section h4 {
                margin: 0 0 8px;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
            }
            th, td {
                border: 1px solid var(--border);
                padding: 10px 12px;
                text-align: left;
            }
            th {
                background: #f9fafb;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.06em;
            }
            .text-right { text-align: right; }
            .summary {
                margin-top: 12px;
                display: grid;
                gap: 8px;
                font-size: 13px;
            }
            .summary strong {
                color: var(--secondary);
            }
            .notes {
                font-size: 12px;
                color: var(--muted);
                line-height: 1.6;
            }
            .signature {
                margin-top: 24px;
                text-align: right;
                font-size: 13px;
            }
            .footer {
                margin-top: 24px;
                padding: 16px 32px;
                background: var(--primary);
                color: #f8fafc;
                font-size: 11px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 12px;
            }
            @media print {
                body { background: #fff; padding: 0; }
                .page { box-shadow: none; border: none; }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="header">
                <div class="brand">
                    <div class="logo-badge">SIVMI</div>
                    <div><strong>Bimbel Gambar Villa Merah</strong></div>
                    <small>Invoice Pendaftaran siswa</small>
                </div>
                <div class="invoice-title" style="background: {{ $isAngsuran ? 'var(--accent)' : 'var(--secondary)' }};">
                    {{ $isAngsuran ? 'TAGIHAN ANGSURAN' : 'INVOICE' }}
                </div>
            </div>

            <div class="meta">
                <div>
                    <h3>Kepada Yth</h3>
                    <p>Orang Tua/Wali Murid dari</p>
                    <p class="tagline">{{ $registration->full_name ?? $registration->name }}</p>
                    <p>Program: {{ $programName }}</p>
                </div>
                <div>
                    <h3>{{ $isAngsuran ? 'Tagihan Angsuran Bimbel' : 'Invoice Pembayaran Lunas' }}</h3>
                    <p>No. {{ $isAngsuran ? 'Tagihan' : 'Invoice' }}: {{ $invoiceNumber }}</p>
                    @if ($isAngsuran)
                        <p>{{ $installmentLabel }}</p>
                    @endif
                    <p>Tanggal: {{ $invoiceDate->format('d-m-Y') }}</p>
                    <p>Jatuh Tempo: {{ $dueDate->format('d-m-Y') }}</p>
                    <div class="badge {{ $isAngsuran ? 'accent' : '' }}">{{ $paymentStatus }}</div>
                </div>
            </div>

            <div class="section">
                <h4>{{ $isAngsuran ? 'Rincian Angsuran' : 'Rincian Tagihan' }}</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th class="text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($isAngsuran)
                            <tr>
                                <td>{{ $installmentLabel }}</td>
                                <td class="text-right">{{ number_format($installmentTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Program</td>
                                <td class="text-right">{{ number_format($totalProgram, 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>Biaya Pendaftaran</td>
                                <td class="text-right">{{ number_format($biayaDaftar, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Biaya Pendidikan</td>
                                <td class="text-right">{{ number_format($biayaPendidikan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td class="text-right">{{ number_format($discount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>Total yang harus dibayar</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoiceTotal, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary">
                    <div>Status Pembayaran: <strong>{{ $paymentStatus }}</strong></div>
                    @if ($isAngsuran && $remainingBalance > 0)
                        <div>Sisa Tagihan: <strong>Rp {{ number_format($remainingBalance, 0, ',', '.') }}</strong></div>
                    @endif
                    <div>Metode Pembayaran: Transfer Bank</div>
                </div>
            </div>

            <div class="section">
                <h4>Rekening Pembayaran</h4>
                <div class="notes">
                    Bank: (isi bank)
                    <br>
                    Atas Nama: (isi nama rekening)
                    <br>
                    No. Rekening: (isi nomor rekening)
                </div>
            </div>

            <div class="section">
                <h4>Keterangan</h4>
                <div class="notes">
                    - Mohon melakukan pembayaran sebelum jatuh tempo.
                    <br>
                    - Konfirmasi pembayaran via WhatsApp admin setelah transfer.
                    <br>
                    - Simpan invoice ini sebagai bukti pendaftaran.
                </div>
                <div class="signature">
                    Hormat kami,
                    <br>
                    Admin &amp; Keuangan
                </div>
            </div>

            <div class="footer">
                <div>
                    Bandung (Pusat)
                    <br>
                    Jl. Anggrek No. 49
                    <br>
                    Telp: 022-7102177
                </div>
                <div>
                    Cabang
                    <br>
                    Jl. Gandaria 1 No. 9A
                    <br>
                    Telp: 021-724675
                </div>
                <div>
                    Jakarta Timur
                    <br>
                    Jl. Ahmad Yani No. 41
                    <br>
                    Telp: 021-29612989
                </div>
            </div>
        </div>
    </body>
</html>
