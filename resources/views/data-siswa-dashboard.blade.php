@extends('layout')

@section('title', 'Dashboard Data Siswa')

@section('content')
    <style>
        .data-siswa-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .data-siswa-chart {
            display: grid;
            gap: 12px;
        }
        .data-siswa-chart-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .data-siswa-chart-title {
            display: grid;
            gap: 4px;
        }
        .data-siswa-chart-title span {
            font-size: 12px;
            color: var(--muted);
        }
        .data-siswa-filter {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: center;
            margin-top: 12px;
        }
        .data-siswa-filter .filter-fields {
            display: grid;
            gap: 10px;
        }
        .data-siswa-filter .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px;
        }
        .data-siswa-filter select,
        .data-siswa-filter button {
            padding: 6px 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 12px;
            font-family: inherit;
        }
        .data-siswa-bars {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(22px, 1fr));
            gap: 6px;
            align-items: end;
            height: 140px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid var(--border);
        }
        .data-siswa-bar {
            display: grid;
            gap: 6px;
            justify-items: center;
        }
        .data-siswa-bar span {
            font-size: 10px;
            color: var(--muted);
        }
        .data-siswa-bar .bar {
            width: 100%;
            min-height: 6px;
            border-radius: 8px 8px 4px 4px;
            background: linear-gradient(180deg, var(--accent), var(--accent-dark));
        }
        .top-school-list {
            display: grid;
            gap: 12px;
        }
        .top-school-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .top-school-name {
            font-weight: 600;
            font-size: 13px;
        }
        .top-school-meta {
            font-size: 12px;
            color: var(--muted);
        }
        .top-school-bar {
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--accent-dark), var(--accent));
        }
        .program-total-list {
            display: grid;
            gap: 12px;
        }
        .program-total-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .program-total-name {
            font-weight: 600;
            font-size: 13px;
        }
        .region-map-wrap {
            position: relative;
            margin-top: 12px;
        }
        .region-map-svg {
            width: 100%;
            height: auto;
            display: block;
        }
        .region-shape {
            fill: rgba(10, 10, 92, var(--heat, 0.15));
            stroke: rgba(10, 10, 92, 0.45);
            stroke-width: 2;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .region-shape:hover {
            fill: rgba(194, 15, 49, calc(var(--heat, 0.2) + 0.2));
            stroke: rgba(194, 15, 49, 0.9);
            transform: translateY(-2px);
        }
        .region-tooltip {
            position: absolute;
            padding: 8px 10px;
            border-radius: 10px;
            background: #0a0a5c;
            color: #fff;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.15s ease, transform 0.15s ease;
            box-shadow: 0 10px 24px rgba(10, 10, 92, 0.2);
        }
        .region-tooltip.active {
            opacity: 1;
            transform: translateY(0);
        }
        .region-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            font-size: 11px;
            color: var(--muted);
        }
        .region-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .region-legend i {
            width: 12px;
            height: 12px;
            border-radius: 4px;
            background: rgba(10, 10, 92, 0.2);
            display: inline-block;
        }
        @media (max-width: 720px) {
            .data-siswa-filter {
                grid-template-columns: 1fr;
            }
            .data-siswa-filter .filter-row {
                grid-template-columns: 1fr;
            }
            .data-siswa-filter .button {
                width: 100%;
            }
        }
    </style>

    <div class="page-header">
        <div>
            <h1>Dashboard Data Siswa</h1>
            <p class="muted">Ringkasan visual pendaftaran berdasarkan filter.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="data-siswa-chart-title">
            <h3>Filter Dashboard</h3>
            <span>Filter akan mempengaruhi seluruh visual data.</span>
        </div>
        <form class="data-siswa-filter" method="GET" action="{{ route('data-siswa.dashboard') }}" id="dashboardFilter">
            <div class="filter-fields">
                <div class="filter-row">
                    <select name="filter_month">
                        <option value="0" @selected($filterMonth === 0)>Semua Bulan</option>
                        @foreach ([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ] as $monthValue => $monthLabel)
                            <option value="{{ $monthValue }}" @selected($filterMonth === $monthValue)>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                    <select name="filter_year">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($filterYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-row">
                    <select name="filter_location">
                        <option value="" @selected($filterLocation === '')>Semua Lokasi</option>
                        @foreach ($locationOptions as $location)
                            <option value="{{ $location }}" @selected($filterLocation === $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                    <select name="filter_program">
                        <option value="" @selected($filterProgram === '')>Semua Program</option>
                        @foreach ($programOptions as $program)
                            <option value="{{ $program }}" @selected($filterProgram === $program)>{{ $program }}</option>
                        @endforeach
                    </select>
                    <select name="filter_kode">
                        <option value="" @selected($filterKode === '')>Semua Kode</option>
                        @foreach ($kodeOptions as $kode)
                            <option value="{{ $kode }}" @selected($filterKode === $kode)>{{ $kode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="data-siswa-dashboard-grid">
        <div class="card data-siswa-chart">
            <div class="data-siswa-chart-header">
                <div class="data-siswa-chart-title">
                    <h3>Grafik Pendaftaran</h3>
                    <span>
                        {{ $chartRangeLabel }}
                        @if ($filterLocation)
                            - Lokasi: {{ $filterLocation }}
                        @endif
                        @if ($filterProgram)
                            - Program: {{ $filterProgram }}
                        @endif
                        @if ($filterKode)
                            - Kode: {{ $filterKode }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="data-siswa-bars">
                @foreach ($chartValues as $idx => $value)
                    @php
                        $height = $chartMax > 0 ? max(6, round(($value / $chartMax) * 120)) : 6;
                    @endphp
                    <div class="data-siswa-bar">
                        <div class="bar" style="height: {{ $height }}px;"></div>
                        <span>{{ $chartLabels[$idx] }}</span>
                        <span>{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="data-siswa-chart-title">
                <h3>Top 5 Sekolah</h3>
                <span>Jumlah pendaftar terbanyak.</span>
            </div>
            <div class="top-school-list">
                @php
                    $topMax = (int) ($topSchools->max('total') ?? 0);
                @endphp
                @forelse ($topSchools as $school)
                    @php
                        $width = $topMax > 0 ? round(($school->total / $topMax) * 100) : 0;
                    @endphp
                    <div>
                        <div class="top-school-row">
                            <div class="top-school-name">{{ $school->school_name }}</div>
                            <div class="top-school-meta">{{ $school->total }}</div>
                        </div>
                        <div class="top-school-bar" style="width: {{ $width }}%;"></div>
                    </div>
                @empty
                    <div class="muted">Belum ada data sekolah untuk filter ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="data-siswa-chart-title">
                <h3>Total Program Bimbel</h3>
                <span>Jumlah pendaftar per program.</span>
            </div>
            <div class="program-total-list">
                @php
                    $programMax = (int) ($programTotals->max('total') ?? 0);
                @endphp
                @forelse ($programTotals as $program)
                    @php
                        $width = $programMax > 0 ? round(($program->total / $programMax) * 100) : 0;
                    @endphp
                    <div>
                        <div class="program-total-row">
                            <div class="program-total-name">{{ $program->program }}</div>
                            <div class="top-school-meta">{{ $program->total }}</div>
                        </div>
                        <div class="top-school-bar" style="width: {{ $width }}%;"></div>
                    </div>
                @empty
                    <div class="muted">Belum ada data program untuk filter ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="data-siswa-chart-title">
                <h3>Peta Wilayah</h3>
                <span>Distribusi pendaftar berdasarkan provinsi.</span>
            </div>
            @php
                $regionLookup = $regionData->keyBy('key');
                $sumatera = $regionLookup->get('SUMATERA');
                $jawa = $regionLookup->get('JAWA');
                $kalimantan = $regionLookup->get('KALIMANTAN');
                $sulawesi = $regionLookup->get('SULAWESI');
                $balinusra = $regionLookup->get('BALI_NUSRA');
                $malpap = $regionLookup->get('MALUKU_PAPUA');
            @endphp
            <div class="region-map-wrap">
                <div class="region-tooltip" id="regionTooltip"></div>
                <svg class="region-map-svg" viewBox="0 0 900 480" aria-label="Peta Indonesia">
                    <title>Peta Indonesia - Distribusi Pendaftar</title>
                    <g>
                        <polygon
                            class="region-shape"
                            data-label="Sumatera"
                            data-total="{{ $sumatera['total'] ?? 0 }}"
                            style="--heat: {{ number_format($sumatera['heat'] ?? 0.12, 2, '.', '') }};"
                            points="70,210 110,170 190,160 260,180 290,230 260,270 210,300 150,310 100,280 70,240"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Jawa"
                            data-total="{{ $jawa['total'] ?? 0 }}"
                            style="--heat: {{ number_format($jawa['heat'] ?? 0.12, 2, '.', '') }};"
                            points="260,330 420,330 470,350 440,370 270,360 235,345"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Kalimantan"
                            data-total="{{ $kalimantan['total'] ?? 0 }}"
                            style="--heat: {{ number_format($kalimantan['heat'] ?? 0.12, 2, '.', '') }};"
                            points="320,150 430,120 530,150 560,220 520,280 430,270 350,230"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Sulawesi"
                            data-total="{{ $sulawesi['total'] ?? 0 }}"
                            style="--heat: {{ number_format($sulawesi['heat'] ?? 0.12, 2, '.', '') }};"
                            points="590,170 640,150 690,170 660,210 710,235 725,280 680,300 650,260 600,245 580,210"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Bali & Nusra"
                            data-total="{{ $balinusra['total'] ?? 0 }}"
                            style="--heat: {{ number_format($balinusra['heat'] ?? 0.12, 2, '.', '') }};"
                            points="470,380 510,385 570,395 600,415 520,420 480,410"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Maluku"
                            data-total="{{ $malpap['total'] ?? 0 }}"
                            style="--heat: {{ number_format($malpap['heat'] ?? 0.12, 2, '.', '') }};"
                            points="700,250 745,250 755,280 720,300 690,280"
                        />
                        <polygon
                            class="region-shape"
                            data-label="Papua"
                            data-total="{{ $malpap['total'] ?? 0 }}"
                            style="--heat: {{ number_format($malpap['heat'] ?? 0.12, 2, '.', '') }};"
                            points="730,200 860,200 890,250 870,300 780,300 740,260"
                        />
                    </g>
                </svg>
                <div class="region-legend">
                    <span><i></i> Lebih terang = lebih banyak pendaftar</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tooltip = document.getElementById('regionTooltip');
            const shapes = document.querySelectorAll('.region-shape');
            const wrap = document.querySelector('.region-map-wrap');
            const filterForm = document.getElementById('dashboardFilter');

            shapes.forEach((shape) => {
                shape.addEventListener('mousemove', (event) => {
                    const label = shape.getAttribute('data-label');
                    const total = shape.getAttribute('data-total');
                    const rect = wrap.getBoundingClientRect();
                    tooltip.textContent = `${label}: ${total}`;
                    tooltip.style.left = `${event.clientX - rect.left + 12}px`;
                    tooltip.style.top = `${event.clientY - rect.top - 10}px`;
                    tooltip.classList.add('active');
                });
                shape.addEventListener('mouseleave', () => {
                    tooltip.classList.remove('active');
                });
            });

            if (filterForm) {
                const selects = filterForm.querySelectorAll('select');
                selects.forEach((select) => {
                    select.addEventListener('change', () => {
                        filterForm.submit();
                    });
                });
            }
        });
    </script>
@endsection
