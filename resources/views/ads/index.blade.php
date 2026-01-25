@extends('layout')

@section('title', 'Ads / Iklan')

@section('content')
    <style>
        .ads-grid {
            display: grid;
            gap: 16px;
        }
        .ads-section-title {
            margin: 0 0 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent-dark);
            font-weight: 700;
        }
        .form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .form-grid.compact {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        }
        .helper {
            font-size: 12px;
            color: var(--muted);
        }
        .metric-table td,
        .metric-table th {
            vertical-align: top;
        }
        .badge-status {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(10, 10, 92, 0.1);
            color: var(--accent-dark);
        }
        .summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
        .summary-card h3 {
            margin: 0 0 6px;
            font-size: 14px;
            color: var(--muted);
        }
        .summary-card strong {
            font-size: 20px;
        }
        .filter-bar {
            display: grid;
            gap: 12px;
        }
        .trend-chart {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
            align-items: end;
            height: 180px;
            padding: 12px 6px;
            border-radius: 12px;
            background: rgba(10, 10, 92, 0.06);
        }
        .trend-item {
            display: grid;
            gap: 6px;
            justify-items: center;
        }
        .trend-bars {
            display: flex;
            gap: 4px;
            align-items: flex-end;
            height: 120px;
        }
        .bar {
            width: 10px;
            border-radius: 6px 6px 2px 2px;
        }
        .bar.leads { background: var(--accent); }
        .bar.closing { background: var(--accent-orange); }
        .trend-label {
            font-size: 10px;
            text-align: center;
            color: var(--muted);
        }
        .trend-legend {
            display: flex;
            gap: 12px;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
            margin-top: 8px;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }
        .legend-leads { background: var(--accent); }
        .legend-closing { background: var(--accent-orange); }
        .trend-change {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 12px;
            font-size: 13px;
        }
        .trend-change strong {
            font-weight: 700;
        }
        .progress {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: rgba(10, 10, 92, 0.1);
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: var(--accent);
        }
        .progress-bar.orange { background: var(--accent-orange); }
        .progress-bar.yellow { background: var(--accent-yellow); }
    </style>

    <h1>Ads / Iklan</h1>
    <p class="muted">Command center kampanye iklan berbayar untuk monitoring, pelaporan, dan evaluasi.</p>

    <div class="card filter-bar" style="margin-bottom: 16px;">
        <div class="ads-section-title">Filter Laporan</div>
        <form class="form" method="GET" action="{{ route('ads.index') }}">
            <div class="form-grid">
                <div>
                    <label>Platform</label>
                    <select name="platform">
                        <option value="">Semua platform</option>
                        @foreach ($platforms as $platform)
                            <option value="{{ $platform }}" @selected(($filters['platform'] ?? '') === $platform)>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Kampanye</label>
                    <select name="campaign_id">
                        <option value="">Semua kampanye</option>
                        @foreach ($campaignOptions as $campaign)
                            <option value="{{ $campaign->id }}" @selected(($filters['campaign_id'] ?? null) == $campaign->id)>
                                {{ $campaign->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>PIC</label>
                    <select name="pic_id">
                        <option value="">Semua PIC</option>
                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id }}" @selected(($filters['pic_id'] ?? null) == $pic->id)>{{ $pic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Periode</label>
                    <select name="period">
                        <option value="daily" @selected(($filters['period'] ?? '') === 'daily')>Harian</option>
                        <option value="weekly" @selected(($filters['period'] ?? '') === 'weekly')>Mingguan</option>
                        <option value="monthly" @selected(($filters['period'] ?? '') === 'monthly')>Bulanan</option>
                    </select>
                </div>
                <div>
                    <label>Mulai</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div>
                    <label>Selesai</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] ?? '' }}">
                </div>
            </div>
            <div class="actions">
                <button class="button" type="submit">Terapkan</button>
                <a class="button button-outline" href="{{ route('ads.index') }}">Reset</a>
                @if ($canReport)
                    <a class="button button-outline" href="{{ route('ads.export.csv', request()->query()) }}">Export Excel</a>
                    <a class="button button-outline" target="_blank" href="{{ route('ads.export.pdf', request()->query()) }}">Export PDF</a>
                @endif
            </div>
        </form>
    </div>

    <div class="summary-grid" style="margin-bottom: 16px;">
        <div class="card summary-card">
            <h3>Total Biaya</h3>
            <strong>Rp {{ number_format((float) ($summary->total_cost ?? 0), 0, ',', '.') }}</strong>
        </div>
        <div class="card summary-card">
            <h3>Total Tayangan</h3>
            <strong>{{ number_format((int) ($summary->total_impressions ?? 0)) }}</strong>
        </div>
        <div class="card summary-card">
            <h3>Total Jangkauan</h3>
            <strong>{{ number_format((int) ($summary->total_reach ?? 0)) }}</strong>
        </div>
        <div class="card summary-card">
            <h3>Total Leads</h3>
            <strong>{{ number_format((int) ($summary->total_leads ?? 0)) }}</strong>
        </div>
        <div class="card summary-card">
            <h3>Total Closing</h3>
            <strong>{{ number_format((int) ($summary->total_closing ?? 0)) }}</strong>
        </div>
    </div>

    <div class="ads-grid">
        <div class="card">
            <div class="ads-section-title">Grafik Performa</div>
            <p class="helper">Perbandingan leads dan closing per periode.</p>
            @if (empty($trendSeries))
                <p class="muted">Belum ada data trend.</p>
            @else
                <div class="trend-chart">
                    @foreach ($trendSeries as $item)
                        @php
                            $leadsHeight = $trendMax > 0 ? round(($item['leads'] / $trendMax) * 100) : 0;
                            $closingHeight = $trendMax > 0 ? round(($item['closing'] / $trendMax) * 100) : 0;
                        @endphp
                        <div class="trend-item">
                            <div class="trend-bars">
                                <span class="bar leads" style="height: {{ $leadsHeight }}%;"></span>
                                <span class="bar closing" style="height: {{ $closingHeight }}%;"></span>
                            </div>
                            <span class="trend-label">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="trend-legend">
                    <span><span class="legend-dot legend-leads"></span> Leads</span>
                    <span><span class="legend-dot legend-closing"></span> Closing</span>
                </div>
                <div class="trend-change">
                    <div>
                        <strong>Leads:</strong>
                        @if ($trendChange['lead_change'])
                            {{ $trendChange['lead_change']['diff'] >= 0 ? '+' : '' }}{{ $trendChange['lead_change']['diff'] }}
                            ({{ $trendChange['lead_change']['percent'] !== null ? number_format($trendChange['lead_change']['percent'], 1) . '%' : '-' }})
                        @else
                            -
                        @endif
                    </div>
                    <div>
                        <strong>Closing:</strong>
                        @if ($trendChange['closing_change'])
                            {{ $trendChange['closing_change']['diff'] >= 0 ? '+' : '' }}{{ $trendChange['closing_change']['diff'] }}
                            ({{ $trendChange['closing_change']['percent'] !== null ? number_format($trendChange['closing_change']['percent'], 1) . '%' : '-' }})
                        @else
                            -
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="ads-section-title">Target vs Realisasi (Per Platform)</div>
            <table>
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Leads</th>
                        <th>Closing</th>
                        <th>Jangkauan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($platformPerformance as $row)
                        @php
                            $leadPercent = $row->kpi_leads > 0 ? min(100, round(($row->actual_leads / $row->kpi_leads) * 100)) : 0;
                            $closingPercent = $row->kpi_closing > 0 ? min(100, round(($row->actual_closing / $row->kpi_closing) * 100)) : 0;
                            $reachPercent = $row->kpi_reach > 0 ? min(100, round(($row->actual_reach / $row->kpi_reach) * 100)) : 0;
                        @endphp
                        <tr>
                            <td>{{ $row->platform }}</td>
                            <td>
                                <div class="helper">{{ $row->actual_leads }} / {{ $row->kpi_leads }}</div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $leadPercent }}%;"></div>
                                </div>
                            </td>
                            <td>
                                <div class="helper">{{ $row->actual_closing }} / {{ $row->kpi_closing }}</div>
                                <div class="progress">
                                    <div class="progress-bar orange" style="width: {{ $closingPercent }}%;"></div>
                                </div>
                            </td>
                            <td>
                                <div class="helper">{{ $row->actual_reach }} / {{ $row->kpi_reach }}</div>
                                <div class="progress">
                                    <div class="progress-bar yellow" style="width: {{ $reachPercent }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">Belum ada target atau realisasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="ads-section-title">Perencanaan Kampanye</div>
            @if (! $canPlan)
                <p class="muted">Akses Anda hanya untuk melihat data kampanye.</p>
            @else
                <form class="form" method="POST" action="{{ route('ads.campaigns.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div>
                            <label>Nama Kampanye</label>
                            <input name="name" type="text" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label>Platform</label>
                            <select name="platform" required>
                                <option value="">Pilih platform</option>
                                @foreach ($platforms as $platform)
                                    <option value="{{ $platform }}" @selected(old('platform') === $platform)>{{ $platform }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Objective</label>
                            <select name="objective" required>
                                <option value="">Pilih objective</option>
                                @foreach ($objectives as $objective)
                                    <option value="{{ $objective }}" @selected(old('objective') === $objective)>{{ $objective }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>PIC</label>
                            <select name="pic_id">
                                <option value="">Pilih PIC</option>
                                @foreach ($pics as $pic)
                                    <option value="{{ $pic->id }}" @selected(old('pic_id') == $pic->id)>{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Budget Plan</label>
                            <input name="budget_plan" type="number" min="0" step="0.01" value="{{ old('budget_plan') }}">
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Mulai Tayang</label>
                            <input name="start_date" type="date" value="{{ old('start_date') }}">
                        </div>
                        <div>
                            <label>Selesai</label>
                            <input name="end_date" type="date" value="{{ old('end_date') }}">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div>
                            <label>KPI Leads</label>
                            <input name="kpi_leads" type="number" min="0" value="{{ old('kpi_leads') }}">
                        </div>
                        <div>
                            <label>KPI Closing</label>
                            <input name="kpi_closing" type="number" min="0" value="{{ old('kpi_closing') }}">
                        </div>
                        <div>
                            <label>KPI Jangkauan</label>
                            <input name="kpi_reach" type="number" min="0" value="{{ old('kpi_reach') }}">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div>
                            <label>Brief Kampanye</label>
                            <textarea name="brief" rows="3">{{ old('brief') }}</textarea>
                        </div>
                        <div>
                            <label>Target Audiens</label>
                            <textarea name="target_audience" rows="3">{{ old('target_audience') }}</textarea>
                        </div>
                    </div>
                    <button class="button" type="submit">Simpan Kampanye</button>
                </form>
            @endif
        </div>

        <div class="card">
            <div class="ads-section-title">Daftar Kampanye</div>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Platform</th>
                        <th>PIC</th>
                        <th>Status</th>
                        <th>Budget</th>
                        <th>KPI (Lead/Closing/Reach)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ $campaign->platform }}</td>
                            <td>{{ $campaign->pic?->name ?? '-' }}</td>
                            <td><span class="badge-status">{{ $campaign->status }}</span></td>
                            <td>Rp {{ number_format((float) $campaign->budget_plan, 0, ',', '.') }}</td>
                            <td>{{ $campaign->kpi_leads }} / {{ $campaign->kpi_closing }} / {{ $campaign->kpi_reach }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Belum ada kampanye.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="ads-section-title">Input Monitoring</div>
            @if (! $canMonitor)
                <p class="muted">Akses Anda hanya untuk melihat laporan.</p>
            @else
                <form class="form" method="POST" action="{{ route('ads.metrics.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div>
                            <label>Kampanye</label>
                            <select name="ads_campaign_id" required>
                                <option value="">Pilih kampanye</option>
                                @foreach ($campaignOptions as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }} - {{ $campaign->platform }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>PIC</label>
                            <select name="pic_id">
                                <option value="">Pilih PIC</option>
                                @foreach ($pics as $pic)
                                    <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Tanggal Laporan</label>
                            <input name="report_date" type="date">
                        </div>
                        <div>
                            <label>Biaya</label>
                            <input name="cost" type="number" min="0" step="0.01">
                        </div>
                        <div>
                            <label>Produk</label>
                            <input name="product" type="text">
                        </div>
                        <div>
                            <label>Link Konten</label>
                            <input name="content_url" type="url" placeholder="https://">
                        </div>
                    </div>

                    <div class="ads-section-title">Metric Utama</div>
                    <div class="form-grid compact">
                        <div>
                            <label>Tayangan</label>
                            <input name="impressions" type="number" min="0">
                        </div>
                        <div>
                            <label>Jangkauan</label>
                            <input name="reach" type="number" min="0">
                        </div>
                        <div>
                            <label>Klik WA</label>
                            <input name="clicks_wa" type="number" min="0">
                        </div>
                        <div>
                            <label>Leads</label>
                            <input name="leads_count" type="number" min="0">
                        </div>
                        <div>
                            <label>Closing</label>
                            <input name="closing_count" type="number" min="0">
                        </div>
                    </div>

                    <div class="ads-section-title">Engagement</div>
                    <div class="form-grid compact">
                        <div>
                            <label>Pemutaran 3 Detik</label>
                            <input name="views_3s" type="number" min="0">
                        </div>
                        <div>
                            <label>Pemutaran 50 Detik</label>
                            <input name="views_50s" type="number" min="0">
                        </div>
                        <div>
                            <label>Suka & Tanggapan</label>
                            <input name="reactions" type="number" min="0">
                        </div>
                        <div>
                            <label>Klik Tautan</label>
                            <input name="link_clicks" type="number" min="0">
                        </div>
                        <div>
                            <label>Simpan</label>
                            <input name="saves" type="number" min="0">
                        </div>
                        <div>
                            <label>Bagikan</label>
                            <input name="shares" type="number" min="0">
                        </div>
                        <div>
                            <label>Kunjungan Profil</label>
                            <input name="profile_visits" type="number" min="0">
                        </div>
                        <div>
                            <label>Mengikuti</label>
                            <input name="follows" type="number" min="0">
                        </div>
                    </div>

                    <div class="ads-section-title">Demografi</div>
                    <div class="form-grid compact">
                        <div>
                            <label>Gender L (%)</label>
                            <input name="gender_male" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>Gender P (%)</label>
                            <input name="gender_female" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>18-24</label>
                            <input name="age_18_24" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>25-34</label>
                            <input name="age_25_34" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>35-44</label>
                            <input name="age_35_44" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>45-54</label>
                            <input name="age_45_54" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>55-64</label>
                            <input name="age_55_64" type="number" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label>65+</label>
                            <input name="age_65_plus" type="number" min="0" max="100" step="0.01">
                        </div>
                    </div>

                    <div class="ads-section-title">Lokasi Teratas (Top 5)</div>
                    <div class="form-grid compact">
                        @for ($i = 0; $i < 5; $i++)
                            <div>
                                <label>Lokasi {{ $i + 1 }}</label>
                                <input name="top_location_name[]" type="text" placeholder="Nama lokasi">
                            </div>
                            <div>
                                <label>%</label>
                                <input name="top_location_percent[]" type="number" min="0" max="100" step="0.01">
                            </div>
                        @endfor
                    </div>

                    <button class="button" type="submit">Simpan Monitoring</button>
                </form>
            @endif
        </div>

        <div class="card">
            <div class="ads-section-title">Ringkasan Per Platform</div>
            @if (! $canReport)
                <p class="muted">Akses Anda hanya untuk melihat data kampanye.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Total Biaya</th>
                            <th>Jangkauan</th>
                            <th>Leads</th>
                            <th>Closing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($platformSummary as $row)
                            <tr>
                                <td>{{ $row->platform }}</td>
                                <td>Rp {{ number_format((float) $row->total_cost, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $row->total_reach) }}</td>
                                <td>{{ number_format((int) $row->total_leads) }}</td>
                                <td>{{ number_format((int) $row->total_closing) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">Belum ada data laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <div class="ads-section-title">Monitoring Terbaru</div>
            <table class="metric-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kampanye</th>
                        <th>Platform</th>
                        <th>Biaya</th>
                        <th>Leads</th>
                        <th>Closing</th>
                        <th>Jangkauan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($metrics as $metric)
                        <tr>
                            <td>{{ $metric->report_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $metric->campaign?->name ?? '-' }}</td>
                            <td>{{ $metric->campaign?->platform ?? '-' }}</td>
                            <td>Rp {{ number_format((float) $metric->cost, 0, ',', '.') }}</td>
                            <td>{{ number_format((int) $metric->leads_count) }}</td>
                            <td>{{ number_format((int) $metric->closing_count) }}</td>
                            <td>{{ number_format((int) $metric->reach) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Belum ada data monitoring.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
