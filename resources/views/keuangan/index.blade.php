@extends('layout')

@section('title', 'Keuangan')

@section('content')
    <style>
        .keuangan-table { font-size: 12px; }
        .keuangan-table th { font-size: 10px; }
        .keuangan-actions { display: grid; gap: 6px; }
        .keuangan-actions .row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .keuangan-actions input { padding: 6px 8px; font-size: 12px; border-radius: 8px; }
        .keuangan-actions .button { padding: 6px 10px; font-size: 12px; }
        .keuangan-proof { font-size: 12px; }
    </style>

    <div class="page-header">
        <div>
            <h1>Keuangan</h1>
            <p class="muted">Pemantauan pembayaran, angsuran, dan rekap keuangan siswa.</p>
        </div>
    </div>

    <div class="card" style="overflow-x:auto;">
        <table class="table keuangan-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Program</th>
                <th>Sistem</th>
                <th>Invoice</th>
                <th>Pembayaran</th>
                <th>Bukti</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($registrations as $index => $reg)
                @php
                    $statusKey = $reg->payment_status ?? 'unpaid';
                    $statusClass = match ($statusKey) {
                        'verified' => 'verified',
                        'rejected' => 'rejected',
                        'submitted' => 'pending',
                        default => 'pending',
                    };
                @endphp
                <tr>
                    <td>{{ $registrations->firstItem() + $index }}</td>
                    <td>{{ optional($reg->created_at)->format('d M Y') }}</td>
                    <td>{{ $reg->full_name ?? $reg->name }}</td>
                    <td>{{ $reg->program ?? '-' }}</td>
                    <td>{{ strtoupper($reg->payment_system ?? '-') }}</td>
                    <td>
                        <div><strong>{{ $reg->invoice_number ?? '-' }}</strong></div>
                        <div class="muted">Rp {{ number_format((int) ($reg->invoice_total ?? 0), 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div class="keuangan-proof">
                            <div>Rp {{ number_format((int) ($reg->payment_amount ?? 0), 0, ',', '.') }}</div>
                            <div class="muted">
                                {{ $reg->payment_verified_at ? 'Verif '.$reg->payment_verified_at->format('d M Y') : 'Belum diverifikasi' }}
                            </div>
                        </div>
                    </td>
                    <td>
                        @if ($reg->payment_proof_path)
                            <a class="button button-outline" href="{{ asset('storage/'.$reg->payment_proof_path) }}" target="_blank" rel="noopener">
                                Lihat Bukti
                            </a>
                        @else
                            <span class="muted">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        <span class="status {{ $statusClass }}">{{ ucfirst($statusKey) }}</span>
                    </td>
                    <td>
                        <div class="keuangan-actions">
                            @if (! $reg->payment_proof_path)
                                <form method="POST" action="{{ route('keuangan.proof', $reg) }}" enctype="multipart/form-data" class="row">
                                    @csrf
                                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <input type="number" name="payment_amount" min="0" placeholder="Nominal">
                                    <button class="button" type="submit">Upload Bukti</button>
                                </form>
                            @endif

                            @if ($reg->payment_proof_path && $statusKey !== 'verified')
                                <form method="POST" action="{{ route('keuangan.verify', $reg) }}">
                                    @csrf
                                    <button class="button button-outline" type="submit">Verifikasi</button>
                                </form>
                            @endif

                            @if ($statusKey === 'verified')
                                <a class="button button-outline" href="{{ route('keuangan.invoice', $reg) }}" target="_blank" rel="noopener">
                                    Invoice Pembayaran
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center muted">Belum ada data pembayaran.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-shell">
        {{ $registrations->links() }}
    </div>
@endsection
