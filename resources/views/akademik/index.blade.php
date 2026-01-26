@extends('layout')

@section('title', 'Akademik')

@section('content')
    <style>
        .akademik-table { font-size: 12px; }
        .akademik-table th { font-size: 10px; }
    </style>

    <div class="page-header">
        <div>
            <h1>Akademik</h1>
            <p class="muted">Ringkasan pengelolaan kelas, absensi, dan kegiatan belajar.</p>
        </div>
    </div>

    <div class="card" style="overflow-x:auto;">
        <table class="table akademik-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Program</th>
                <th>Kelas</th>
                <th>Lokasi</th>
                <th>Status Keuangan</th>
                <th>Status Akademik</th>
                <th>Diteruskan</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($students as $index => $student)
                @php
                    $paymentStatus = $student->payment_status ?? 'unpaid';
                    $paymentClass = match ($paymentStatus) {
                        'verified' => 'verified',
                        'rejected' => 'rejected',
                        'submitted' => 'pending',
                        default => 'pending',
                    };
                    $academicStatus = $paymentStatus === 'verified' ? 'Diteruskan ke Akademik' : 'Menunggu Keuangan';
                @endphp
                <tr>
                    <td>{{ $students->firstItem() + $index }}</td>
                    <td>{{ $student->full_name ?? $student->name }}</td>
                    <td>{{ $student->program ?? '-' }}</td>
                    <td>{{ $student->class_level ?? '-' }}</td>
                    <td>{{ $student->study_location ?? '-' }}</td>
                    <td>
                        <span class="status {{ $paymentClass }}">{{ ucfirst($paymentStatus) }}</span>
                    </td>
                    <td>{{ $academicStatus }}</td>
                    <td>
                        <div>{{ $student->academic_forwarded_at ? $student->academic_forwarded_at->format('d M Y H:i') : '-' }}</div>
                        <div class="muted">
                            {{ $student->academicForwardedBy?->name ?? '-' }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center muted">Belum ada data siswa tervalidasi.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-shell">
        {{ $students->links() }}
    </div>
@endsection
