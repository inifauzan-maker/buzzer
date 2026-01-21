@extends('layout')

@section('title', 'Target Tim Admin')

@section('content')
    <style>
        .section-block {
            margin: 18px 0;
        }
        .target-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .bar-cell {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }
        .bar-chart {
            position: relative;
            height: 110px;
            padding: 10px 8px 14px 12px;
            display: grid;
            grid-template-rows: 1fr auto;
            gap: 6px;
            background: repeating-linear-gradient(
                to top,
                rgba(148, 163, 184, 0.25) 0,
                rgba(148, 163, 184, 0.25) 1px,
                transparent 1px,
                transparent 28px
            );
            border-left: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            border-radius: 8px;
        }
        .bar-stack {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 70px;
            justify-content: center;
        }
        .bar {
            width: 22px;
            border-radius: 6px 6px 4px 4px;
            background: #94a3b8;
        }
        .bar.achieved { background: #12b5c9; }
        .bar.target { background: #93c5fd; }
        .bar.leads { background: #f97316; }
        .bar-values {
            display: flex;
            justify-content: center;
            gap: 8px;
            font-size: 11px;
            color: var(--muted);
        }
    </style>

    <h1>Target Tim (Admin)</h1>
    <p class="muted">Ringkasan target tahunan tim dan anggota.</p>

    <div class="card section-block">
        <form method="GET" action="{{ route('targets.admin') }}" class="form">
            <div class="target-form-grid">
                <div>
                    <label for="year">Tahun</label>
                    <select id="year" name="year">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($selectedYear == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="button" type="submit">Terapkan Filter</button>
        </form>
    </div>

    <div class="card section-block">
        <h2>Target Tim</h2>
        <table>
            <thead>
                <tr>
                    <th>Tim</th>
                    <th>Target Closing</th>
                    <th>Pencapaian Closing</th>
                    <th>Target Leads</th>
                    <th>Pencapaian Leads</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teamRows as $team)
                    <tr>
                        <td><strong>{{ $team['name'] }}</strong></td>
                        <td>{{ number_format($team['target_closing'], 0, ',', '.') }}</td>
                        <td>
                            @php
                                $closingMax = max($team['target_closing'], $team['closing_achieved']);
                                $closingTargetHeight = $closingMax > 0 ? round(($team['target_closing'] / $closingMax) * 100) : 0;
                                $closingAchievedHeight = $closingMax > 0 ? round(($team['closing_achieved'] / $closingMax) * 100) : 0;
                            @endphp
                            <div class="bar-cell">
                                <div class="bar-chart">
                                    <div class="bar-stack">
                                        <div class="bar target" style="height: {{ $closingTargetHeight }}%;"></div>
                                        <div class="bar achieved" style="height: {{ $closingAchievedHeight }}%;"></div>
                                    </div>
                                    <div class="bar-values">
                                        <span>Target {{ number_format($team['target_closing'], 0, ',', '.') }}</span>
                                        <span>Pencapaian {{ number_format($team['closing_achieved'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="muted">{{ $team['closing_percent'] }}%</span>
                            </div>
                        </td>
                        <td>{{ number_format($team['target_leads'], 0, ',', '.') }}</td>
                        <td>
                            @php
                                $leadsMax = max($team['target_leads'], $team['leads_achieved']);
                                $leadsTargetHeight = $leadsMax > 0 ? round(($team['target_leads'] / $leadsMax) * 100) : 0;
                                $leadsAchievedHeight = $leadsMax > 0 ? round(($team['leads_achieved'] / $leadsMax) * 100) : 0;
                            @endphp
                            <div class="bar-cell">
                                <div class="bar-chart">
                                    <div class="bar-stack">
                                        <div class="bar target" style="height: {{ $leadsTargetHeight }}%;"></div>
                                        <div class="bar leads" style="height: {{ $leadsAchievedHeight }}%;"></div>
                                    </div>
                                    <div class="bar-values">
                                        <span>Target {{ number_format($team['target_leads'], 0, ',', '.') }}</span>
                                        <span>Pencapaian {{ number_format($team['leads_achieved'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="muted">{{ $team['leads_percent'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">Belum ada data target tim.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card section-block">
        <h2>Target Anggota</h2>
        <table>
            <thead>
                <tr>
                    <th>Anggota</th>
                    <th>Tim</th>
                    <th>Target Closing</th>
                    <th>Pencapaian Closing</th>
                    <th>Target Leads</th>
                    <th>Pencapaian Leads</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($memberRows as $member)
                    <tr>
                        <td><strong>{{ $member['name'] }}</strong></td>
                        <td>{{ $member['team'] }}</td>
                        <td>{{ number_format($member['target_closing'], 0, ',', '.') }}</td>
                        <td>
                            @php
                                $memberClosingMax = max($member['target_closing'], $member['closing_achieved']);
                                $memberClosingTargetHeight = $memberClosingMax > 0 ? round(($member['target_closing'] / $memberClosingMax) * 100) : 0;
                                $memberClosingAchievedHeight = $memberClosingMax > 0 ? round(($member['closing_achieved'] / $memberClosingMax) * 100) : 0;
                            @endphp
                            <div class="bar-cell">
                                <div class="bar-chart">
                                    <div class="bar-stack">
                                        <div class="bar target" style="height: {{ $memberClosingTargetHeight }}%;"></div>
                                        <div class="bar achieved" style="height: {{ $memberClosingAchievedHeight }}%;"></div>
                                    </div>
                                    <div class="bar-values">
                                        <span>Target {{ number_format($member['target_closing'], 0, ',', '.') }}</span>
                                        <span>Pencapaian {{ number_format($member['closing_achieved'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="muted">{{ $member['closing_percent'] }}%</span>
                            </div>
                        </td>
                        <td>{{ number_format($member['target_leads'], 0, ',', '.') }}</td>
                        <td>
                            @php
                                $memberLeadsMax = max($member['target_leads'], $member['leads_achieved']);
                                $memberLeadsTargetHeight = $memberLeadsMax > 0 ? round(($member['target_leads'] / $memberLeadsMax) * 100) : 0;
                                $memberLeadsAchievedHeight = $memberLeadsMax > 0 ? round(($member['leads_achieved'] / $memberLeadsMax) * 100) : 0;
                            @endphp
                            <div class="bar-cell">
                                <div class="bar-chart">
                                    <div class="bar-stack">
                                        <div class="bar target" style="height: {{ $memberLeadsTargetHeight }}%;"></div>
                                        <div class="bar leads" style="height: {{ $memberLeadsAchievedHeight }}%;"></div>
                                    </div>
                                    <div class="bar-values">
                                        <span>Target {{ number_format($member['target_leads'], 0, ',', '.') }}</span>
                                        <span>Pencapaian {{ number_format($member['leads_achieved'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="muted">{{ $member['leads_percent'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Belum ada data target anggota.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
