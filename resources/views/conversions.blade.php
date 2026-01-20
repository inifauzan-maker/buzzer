@extends('layout')

@section('title', 'Konversi Lead & Closing')

@section('content')
    <h1>Konversi Lead & Closing</h1>
    <p class="muted">Input dan verifikasi konversi lead/closing.</p>

    <div class="actions" style="margin-bottom: 16px;">
        <a class="button" href="{{ route('conversions.create') }}">Input Konversi</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tim / User</th>
                    <th>Type</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Poin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($conversions as $conversion)
                    <tr>
                        <td>{{ $conversion->created_at?->format('d M Y') }}</td>
                        <td>
                            {{ $conversion->team?->team_name ?? '-' }}
                            <div class="muted">{{ $conversion->user?->name ?? '-' }}</div>
                        </td>
                        <td>{{ $conversion->type }}</td>
                        <td>{{ $conversion->amount }}</td>
                        <td>
                            <span class="status {{ strtolower($conversion->status) }}">{{ $conversion->status }}</span>
                        </td>
                        <td>{{ $conversion->computed_points ? number_format($conversion->computed_points, 2) : '-' }}</td>
                        <td>
                            @if ($conversion->proof_file)
                                <a class="button button-outline" target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($conversion->proof_file) }}">Bukti</a>
                            @endif
                            @if ($conversion->status === 'Pending' && auth()->user()->role !== 'staff')
                                <form method="POST" action="{{ route('conversions.verify', $conversion) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button" type="submit">Verify</button>
                                </form>
                                <form method="POST" action="{{ route('conversions.reject', $conversion) }}" style="margin-top: 8px;">
                                    @csrf
                                    <button class="button button-outline" type="submit">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">Belum ada konversi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 12px;">
            {{ $conversions->links() }}
        </div>
    </div>
@endsection
