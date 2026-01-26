<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invoice Pembayaran {{ $invoiceNumber }}</title>
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
                grid-template-columns: 1fr 260px;
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
                background: var(--primary);
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
                letter-spacing: 0.25em;
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
            .section {
                padding: 0 32px 20px;
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
                    <small>Invoice pembayaran siswa</small>
                </div>
                <div class="invoice-title">INVOICE PEMBAYARAN</div>
            </div>

            <div class="meta">
                <div>
                    <h3>Kepada Yth</h3>
                    <p>Orang Tua/Wali Murid dari</p>
                    <p><strong>{{ $registration->full_name ?? $registration->name }}</strong></p>
                    <p>Program: {{ $programName }}</p>
                </div>
                <div>
                    <h3>Detail Pembayaran</h3>
                    <p>No. Invoice: {{ $invoiceNumber }}</p>
                    <p>Tanggal: {{ $paymentDate->format('d-m-Y') }}</p>
                    <p>Sistem Pembayaran: {{ $paymentSystem ?: '-' }}</p>
                    <p>Status: <strong>{{ $paymentStatus }}</strong></p>
                </div>
            </div>

            <div class="section">
                <h4>Rincian Pembayaran</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th class="text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pembayaran {{ $programName }}</td>
                            <td class="text-right">{{ number_format($paymentAmount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td class="text-right"><strong>{{ number_format($paymentAmount, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary">
                    <div>Metode Pembayaran: Transfer Bank</div>
                    <div>Mohon simpan invoice ini sebagai bukti pembayaran.</div>
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
                    - Pembayaran telah diverifikasi oleh bagian keuangan.
                    <br>
                    - Silakan hubungi admin jika ada pertanyaan.
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
