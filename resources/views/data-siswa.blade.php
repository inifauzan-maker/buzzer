@extends('layout')

@section('title', 'Data Siswa')

@section('content')
    <style>
        .data-siswa-table { font-size: 12px; }
        .data-siswa-table th { font-size: 10px; }
        .data-siswa-table td { vertical-align: top; }
        .data-siswa-table .muted { font-size: 11px; }
        .data-siswa-invoice { font-size: 12px; line-height: 1.35; }
        .data-siswa-actions { display: grid; gap: 8px; align-items: start; }
        .data-siswa-actions .row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .data-siswa-actions .button { padding: 6px 10px; font-size: 12px; }
        .data-siswa-actions input { padding: 6px 8px; font-size: 12px; border-radius: 8px; }
        .data-siswa-actions .meta { font-size: 11px; color: var(--muted); }
    </style>

    <div class="page-header">
        <div>
            <h1>Data Siswa</h1>
            <p class="muted">Daftar pendaftar dari form publik.</p>
        </div>
    </div>

    <div class="card" style="overflow-x:auto;">
        <table class="table data-siswa-table">
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
                <th>Status</th>
                <th>Invoice</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($registrations as $index => $reg)
                @php
                    $programItem = $reg->programItem;
                    $invoicePreview = $programItem
                        ? max(0, (int) $programItem->biaya_daftar + (int) $programItem->biaya_pendidikan - (int) $programItem->discount)
                        : null;
                    $invoiceTotal = $reg->invoice_total ?? $invoicePreview;
                    $statusKey = $reg->validation_status ?? 'pending';
                    $statusClass = match ($statusKey) {
                        'validated' => 'verified',
                        'rejected' => 'rejected',
                        default => 'pending',
                    };
                    $invoiceType = strtolower($reg->payment_system ?? '') === 'angsuran' ? 'angsuran' : 'lunas';
                @endphp
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
                    <td>
                        <span class="status {{ $statusClass }}">{{ ucfirst($statusKey) }}</span>
                    </td>
                    <td class="data-siswa-invoice">
                        <div><strong>{{ $reg->invoice_number ?? '-' }}</strong></div>
                        <div class="muted">
                            Rp {{ $invoiceTotal !== null ? number_format((int) $invoiceTotal, 0, ',', '.') : '-' }}
                        </div>
                        <div class="muted">
                            {{ $reg->invoice_sent_at ? 'Terkirim '.$reg->invoice_sent_at->format('d M Y H:i') : 'Belum dikirim' }}
                        </div>
                        @if ($reg->remaining_balance !== null)
                            <div class="muted">
                                Sisa: Rp {{ number_format((int) $reg->remaining_balance, 0, ',', '.') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="data-siswa-actions">
                            <div class="row">
                                @if ($statusKey !== 'validated')
                                    <form method="POST" action="{{ route('data-siswa.validate', $reg) }}" class="row">
                                        @csrf
                                        @if ($invoicePreview === null && ! $reg->invoice_total)
                                            <input
                                                type="number"
                                                name="invoice_total"
                                                placeholder="Total invoice (Rp)"
                                                min="0"
                                                style="width: 140px;"
                                                required
                                            >
                                        @endif
                                        <button class="button" type="submit">Validasi &amp; Buat Invoice</button>
                                    </form>
                                @else
                                    <span class="meta">Sudah valid</span>
                                @endif

                                <form method="POST" action="{{ route('data-siswa.send-invoice', $reg) }}">
                                    @csrf
                                    <button
                                        class="button button-outline"
                                        type="submit"
                                        @disabled($statusKey !== 'validated' || ! $reg->invoice_total || $reg->invoice_sent_at)
                                    >
                                        {{ $reg->invoice_sent_at ? 'Invoice Terkirim' : 'Kirim Invoice' }}
                                    </button>
                                </form>
                            </div>

                            @if ($statusKey === 'validated' && $invoiceTotal !== null)
                                @php
                                    $invoiceLunasUrl = route('data-siswa.invoice', $reg).'?type=lunas&status=LUNAS';
                                @endphp
                                <form
                                    method="GET"
                                    action="{{ route('data-siswa.invoice', $reg) }}"
                                    target="_blank"
                                    class="row"
                                >
                                    <a
                                        class="button button-outline"
                                        href="{{ $invoiceLunasUrl }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Invoice Lunas
                                    </a>
                                    <input type="hidden" name="type" value="angsuran">
                                    <input type="hidden" name="status" value="ANGSURAN">
                                    <input
                                        type="number"
                                        name="installment_no"
                                        min="1"
                                        placeholder="Ke-"
                                        style="width: 60px;"
                                        required
                                    >
                                    <input
                                        type="number"
                                        name="installment_total"
                                        min="0"
                                        placeholder="Nominal"
                                        value="{{ (int) $invoiceTotal }}"
                                        style="width: 110px;"
                                        required
                                    >
                                    <input
                                        type="number"
                                        name="remaining_balance"
                                        min="0"
                                        placeholder="Sisa tagihan"
                                        style="width: 120px;"
                                    >
                                    <input
                                        type="text"
                                        name="installment_label"
                                        placeholder="Label lengkap (opsional)"
                                        style="width: 160px;"
                                    >
                                    <button class="button button-outline" type="submit">Invoice Angsuran</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center muted">Belum ada data pendaftaran.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-shell">
        {{ $registrations->links() }}
    </div>
@endsection
