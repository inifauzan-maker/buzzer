@extends('layout')

@section('title', 'Settings Poin')

@section('content')
    <h1>Settings Poin</h1>
    <p class="muted">Ubah bobot poin untuk tiap indikator.</p>

    <div class="card">
        <form method="POST" action="{{ route('settings.points.update') }}">
            @csrf
            <table>
                <thead>
                    <tr>
                        <th>Indikator</th>
                        <th>Bobot Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>
                                <input
                                    type="number"
                                    step="0.0001"
                                    name="point_settings[{{ $row['metric'] }}]"
                                    value="{{ old('point_settings.' . $row['metric'], $row['value']) }}"
                                    required
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 16px;">
                <button class="button" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
