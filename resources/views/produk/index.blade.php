@extends('layout')

@section('title', 'Produk')

@section('content')
    <style>
        .produk-shell {
            display: grid;
            gap: 18px;
        }

        .filter-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 14px 16px;
            display: grid;
            gap: 10px;
            box-shadow: var(--shadow);
        }

        .filter-row {
            display: grid;
            gap: 10px;
        }

        .filter-row label {
            display: grid;
            gap: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
        }

        .filter-row select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #f9fafb;
            color: var(--ink);
            font-size: 14px;
        }

        .filter-row select option {
            color: #0e0e0e;
        }

        .notice,
        .errors {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 12px 16px;
            font-size: 13px;
            box-shadow: var(--shadow);
        }

        .errors ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }

        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .form-title {
            margin: 0 0 14px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .form-grid {
            display: grid;
            gap: 12px;
        }

        .form-grid label {
            display: grid;
            gap: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
        }

        .field-hint {
            font-size: 11px;
            text-transform: none;
            letter-spacing: 0.03em;
            opacity: 0.75;
        }

        .form-grid input,
        .form-grid select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #f9fafb;
            color: var(--ink);
            font-size: 14px;
        }

        .form-grid select option {
            color: #0e0e0e;
        }

        .is-hidden {
            display: none;
        }

        .form-actions {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            border-radius: 999px;
            border: none;
            padding: 8px 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
        }

        .btn-ghost {
            background: #f9fafb;
            color: var(--ink);
            border: 1px solid var(--border);
            text-decoration: none;
        }

        .sheet-list {
            display: grid;
            gap: 20px;
        }

        .sheet {
            background: #ffffff;
            color: var(--ink);
            border-radius: 16px;
            padding: 12px 12px 16px;
            box-shadow: var(--shadow);
        }

        .sheet-title {
            margin: 2px 0 6px;
            text-align: center;
            color: var(--accent-dark);
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .sheet-subtitle {
            margin: 0 0 8px;
            text-align: center;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .sheet-table-wrap {
            overflow-x: auto;
        }

        .sheet-table {
            width: 100%;
            min-width: 840px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .sheet-table th,
        .sheet-table td {
            border: 1px solid #cfd8e3;
            padding: 6px 6px;
            text-align: center;
        }

        .sheet-table th {
            background: #f0f9fb;
            font-weight: 700;
        }

        .sheet-table td {
            background: #ffffff;
        }

        .sheet-table .left {
            text-align: left;
        }

        .sheet-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .sheet-actions a,
        .sheet-actions button {
            border: 1px solid var(--accent);
            background: #ffffff;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            cursor: pointer;
            color: var(--accent-dark);
        }

        @media (min-width: 720px) {
            .form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-row {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto auto;
                align-items: end;
            }
        }
    </style>

    <div class="produk-shell">
        <div>
            <h1>Produk</h1>
            <p class="muted">Kelola data produk bimbel.</p>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                Validasi gagal:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $user = auth()->user();
            $isAdmin = $user?->role === 'superadmin';
            $canCreate = $isAdmin;
            $canUpdate = $isAdmin;
            $canDelete = $isAdmin;
        @endphp

        <section class="filter-card">
            <form method="GET" class="filter-row">
                <label>
                    Filter Program Bimbel
                    <select name="program">
                        <option value="">Semua Program</option>
                        @foreach ($programs as $key => $label)
                            <option value="{{ $key }}" @selected($selectedProgram === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Filter Tahun Ajaran
                    <select name="tahun_ajaran">
                        <option value="">Semua Tahun</option>
                        @foreach ($tahunOptions as $option)
                            <option value="{{ $option }}" @selected($selectedTahun === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Terapkan</button>
                @if ($selectedTahun || $selectedProgram)
                    <a class="btn btn-ghost" href="{{ route('produk.index') }}">Reset</a>
                @endif
            </form>
        </section>

        @if ($canCreate || ($editItem && $canUpdate))
            <section class="form-card">
                <h2 class="form-title">
                    {{ $editItem ? 'Edit Data Produk' : 'Tambah Data Produk' }}
                </h2>
                <form
                    method="POST"
                    action="{{ $editItem ? route('produk.update', $editItem) : route('produk.store') }}"
                >
                    @csrf
                    @if ($editItem)
                        @method('PUT')
                    @endif
                    @if ($selectedTahun)
                        <input type="hidden" name="filter_tahun" value="{{ $selectedTahun }}" />
                    @endif
                    @if ($selectedProgram)
                        <input type="hidden" name="filter_program" value="{{ $selectedProgram }}" />
                    @endif
                    @php
                        $selectedProgramValue = old('program', $editItem?->program ?? $selectedProgram);
                        $isCustomProgram = $selectedProgramValue && !array_key_exists($selectedProgramValue, $programs);
                        $resolvedProgramValue = $isCustomProgram ? 'custom' : $selectedProgramValue;
                        $customProgramValue = $isCustomProgram ? $selectedProgramValue : old('program_custom');
                    @endphp
                    <div class="form-grid">
                        <label>
                            Program Bimbel
                            <select name="program" id="programSelect" required>
                                <option value="">Pilih program</option>
                                @foreach ($programs as $key => $label)
                                    <option value="{{ $key }}" @selected($resolvedProgramValue === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                                <option value="custom" @selected($resolvedProgramValue === 'custom')>
                                    Lainnya (isi manual)
                                </option>
                            </select>
                        </label>
                        <label id="programCustomWrap" class="{{ $resolvedProgramValue === 'custom' ? '' : 'is-hidden' }}">
                            Program Bimbel (Lainnya)
                            <input
                                type="text"
                                name="program_custom"
                                id="programCustomInput"
                                value="{{ $customProgramValue }}"
                                placeholder="Isi jika program belum ada"
                            />
                            <span class="field-hint">Kosongkan jika memilih dari daftar.</span>
                        </label>
                        <label>
                            Tahun Ajaran
                            <select name="tahun_ajaran" required>
                                @foreach ($tahunOptions as $option)
                                    <option value="{{ $option }}" @selected(old('tahun_ajaran', $editItem?->tahun_ajaran ?? $selectedTahun) === $option)>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Kode 1
                            <input type="text" name="kode_1" value="{{ old('kode_1', $editItem?->kode_1) }}" required />
                        </label>
                        <label>
                            Kode 2
                            <input type="text" name="kode_2" value="{{ old('kode_2', $editItem?->kode_2) }}" required />
                        </label>
                        <label>
                            Kode 3
                            <input type="text" name="kode_3" value="{{ old('kode_3', $editItem?->kode_3) }}" required />
                        </label>
                        <label>
                            Kode 4
                            <input type="text" name="kode_4" value="{{ old('kode_4', $editItem?->kode_4) }}" required />
                        </label>
                        <label>
                            Nama Program
                            <input type="text" name="nama" value="{{ old('nama', $editItem?->nama) }}" required />
                        </label>
                        <label>
                            Biaya Daftar
                            <input type="number" name="biaya_daftar" value="{{ old('biaya_daftar', $editItem?->biaya_daftar) }}" required />
                        </label>
                        <label>
                            Biaya Pendidikan
                            <input type="number" name="biaya_pendidikan" value="{{ old('biaya_pendidikan', $editItem?->biaya_pendidikan) }}" required />
                        </label>
                        <label>
                            Discount
                            <input type="number" name="discount" value="{{ old('discount', $editItem?->discount ?? 0) }}" required />
                        </label>
                        <label>
                            Jumlah Siswa
                            <input type="number" name="siswa" value="{{ old('siswa', $editItem?->siswa ?? 0) }}" required />
                        </label>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">
                            {{ $editItem ? 'Simpan Perubahan' : 'Simpan' }}
                        </button>
                        @if ($editItem)
                            <a class="btn btn-ghost" href="{{ route('produk.index') }}">Batal</a>
                        @endif
                    </div>
                </form>
            </section>
        @endif

        <section class="sheet-list">
            @forelse ($grouped as $key => $groupItems)
                <div class="sheet">
                    <div class="sheet-title">{{ $programs[$key] ?? $key }}</div>
                    @if (\App\Models\ProdukItem::programSubtitle($key))
                        <div class="sheet-subtitle">
                            {{ \App\Models\ProdukItem::programSubtitle($key) }}
                        </div>
                    @endif
                    <div class="sheet-table-wrap">
                        <table class="sheet-table">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kode 1</th>
                                    <th>Kode 2</th>
                                    <th>Kode 3</th>
                                    <th>Kode 4</th>
                                    <th>Nama Program</th>
                                    <th>Biaya Daftar</th>
                                    <th>Biaya Pendidikan</th>
                                    <th>Discount</th>
                                    <th>Siswa</th>
                                    <th>Omzet</th>
                                    @if ($canUpdate || $canDelete)
                                        <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupItems as $item)
                                    <tr>
                                        <td>{{ strtoupper($item->program) }}</td>
                                        <td>{{ $item->tahun_ajaran }}</td>
                                        <td>{{ $item->kode_1 }}</td>
                                        <td>{{ $item->kode_2 }}</td>
                                        <td>{{ $item->kode_3 }}</td>
                                        <td>{{ $item->kode_4 }}</td>
                                        <td class="left">{{ $item->nama }}</td>
                                        <td>{{ number_format($item->biaya_daftar) }}</td>
                                        <td>{{ number_format($item->biaya_pendidikan) }}</td>
                                        <td>{{ number_format($item->discount) }}</td>
                                        <td>{{ number_format($item->siswa) }}</td>
                                        <td>{{ number_format($item->omzet) }}</td>
                                        @if ($canUpdate || $canDelete)
                                            <td>
                                                <div class="sheet-actions">
                                                    @if ($canUpdate)
                                                        @php
                                                            $editParams = ['produk' => $item];
                                                            if ($selectedTahun) {
                                                                $editParams['tahun_ajaran'] = $selectedTahun;
                                                            }
                                                            if ($selectedProgram) {
                                                                $editParams['program'] = $selectedProgram;
                                                            }
                                                        @endphp
                                                        <a href="{{ route('produk.edit', $editParams) }}">Edit</a>
                                                    @endif
                                                    @if ($canDelete)
                                                        <form
                                                            method="POST"
                                                            action="{{ route('produk.destroy', $item) }}"
                                                            onsubmit="return confirm('Hapus data produk ini?')"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit">Hapus</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="sheet">
                    <div class="sheet-title">Belum ada data produk</div>
                </div>
            @endforelse
        </section>
    </div>

    <script>
        const programSelect = document.getElementById("programSelect");
        const programCustomWrap = document.getElementById("programCustomWrap");
        const programCustomInput = document.getElementById("programCustomInput");
        if (programSelect && programCustomWrap && programCustomInput) {
            const toggleCustom = () => {
                const isCustom = programSelect.value === "custom";
                programCustomWrap.classList.toggle("is-hidden", !isCustom);
                programCustomInput.required = isCustom;
            };
            programSelect.addEventListener("change", toggleCustom);
            toggleCustom();
        }
    </script>
@endsection
