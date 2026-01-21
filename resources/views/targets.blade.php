@extends('layout')

@section('title', 'Target Tim')

@section('content')
    <style>
        .section-block {
            margin: 18px 0;
        }
        .target-top {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .target-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .target-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .member-table th,
        .member-table td {
            vertical-align: top;
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

    <h1>Target Tim</h1>
    <p class="muted">Atur target closing dan leads tahunan untuk tim Anda, lalu pantau peraihannya.</p>

    <div class="target-top section-block">
        <div class="card">
            <h3>Filter Periode</h3>
            <form method="GET" action="{{ route('targets.index') }}" class="form">
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
        <div class="card">
            <h3>Target Tim (Tahun {{ $selectedYear }})</h3>
            <form method="POST" action="{{ route('targets.store') }}" class="form">
                @csrf
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <div class="target-form-grid">
                    <div>
                        <label for="target_closing">Target Closing</label>
                        <input id="target_closing" name="target_closing" type="number" min="0"
                               value="{{ old('target_closing', $targetClosing) }}" required>
                    </div>
                    <div>
                        <label for="target_leads">Target Leads</label>
                        <input id="target_leads" name="target_leads" type="number" min="0"
                               value="{{ old('target_leads', $targetLeads) }}" required>
                    </div>
                </div>
                <button class="button" type="submit">Simpan Target</button>
            </form>
        </div>
    </div>

    <div class="card section-block">
        <h2>Target Anggota Tim</h2>
        @if ($memberRows->isEmpty())
            <p class="muted">Belum ada anggota tim (staff) untuk diberikan target.</p>
        @else
            <form method="POST" action="{{ route('targets.members.store') }}" class="form">
                @csrf
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <table class="member-table">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Target Closing</th>
                            <th>Pencapaian Closing</th>
                            <th>Target Leads</th>
                            <th>Pencapaian Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($memberRows as $member)
                            @php
                                $memberClosingMax = max($member['target_closing'], $member['closing_achieved']);
                                $memberClosingTargetHeight = $memberClosingMax > 0 ? round(($member['target_closing'] / $memberClosingMax) * 100) : 0;
                                $memberClosingAchievedHeight = $memberClosingMax > 0 ? round(($member['closing_achieved'] / $memberClosingMax) * 100) : 0;
                                $memberLeadsMax = max($member['target_leads'], $member['leads_achieved']);
                                $memberLeadsTargetHeight = $memberLeadsMax > 0 ? round(($member['target_leads'] / $memberLeadsMax) * 100) : 0;
                                $memberLeadsAchievedHeight = $memberLeadsMax > 0 ? round(($member['leads_achieved'] / $memberLeadsMax) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $member['name'] }}</strong>
                                    <div class="muted">{{ strtoupper($member['role']) }}</div>
                                </td>
                                <td>
                                    <input type="hidden" name="targets[{{ $member['id'] }}][user_id]" value="{{ $member['id'] }}">
                                    <input type="number"
                                           name="targets[{{ $member['id'] }}][target_closing]"
                                           min="0"
                                           value="{{ old('targets.'.$member['id'].'.target_closing', $member['target_closing']) }}"
                                           required>
                                </td>
                                <td>
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
                                <td>
                                    <input type="number"
                                           name="targets[{{ $member['id'] }}][target_leads]"
                                           min="0"
                                           value="{{ old('targets.'.$member['id'].'.target_leads', $member['target_leads']) }}"
                                           required>
                                </td>
                                <td>
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
                        @endforeach
                    </tbody>
                </table>
                <button class="button" type="submit">Simpan Target Anggota</button>
            </form>
        @endif
    </div>

    <div class="target-grid section-block">
        <div class="card">
            <h3>Target Closing</h3>
            @php
                $closingMax = max($targetClosing, $closingAchieved);
                $closingTargetHeight = $closingMax > 0 ? round(($targetClosing / $closingMax) * 100) : 0;
                $closingAchievedHeight = $closingMax > 0 ? round(($closingAchieved / $closingMax) * 100) : 0;
            @endphp
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $closingTargetHeight }}%;"></div>
                        <div class="bar achieved" style="height: {{ $closingAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($targetClosing, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($closingAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <span class="muted">{{ $closingPercent }}%</span>
            </div>
        </div>
        <div class="card">
            <h3>Target Leads</h3>
            @php
                $leadsMax = max($targetLeads, $leadsAchieved);
                $leadsTargetHeight = $leadsMax > 0 ? round(($targetLeads / $leadsMax) * 100) : 0;
                $leadsAchievedHeight = $leadsMax > 0 ? round(($leadsAchieved / $leadsMax) * 100) : 0;
            @endphp
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $leadsTargetHeight }}%;"></div>
                        <div class="bar leads" style="height: {{ $leadsAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($targetLeads, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($leadsAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <span class="muted">{{ $leadsPercent }}%</span>
            </div>
        </div>
    </div>
@endsection
