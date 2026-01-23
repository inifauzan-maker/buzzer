<div class="profile-grid">
    <div class="card">
        <h3>Visual Poin per Platform</h3>
        @php
            $totalPlatformPoints = $platformPoints->sum('total_points');
            $colors = ['#12b5c9', '#3f475d', '#f97316', '#22c55e', '#ef4444', '#8b5cf6'];
            $segments = [];
            $current = 0;
            foreach ($platformPoints as $index => $row) {
                $percent = $totalPlatformPoints > 0 ? ($row->total_points / $totalPlatformPoints) * 100 : 0;
                $end = $current + $percent;
                $color = $colors[$index % count($colors)];
                $segments[] = $color.' '.$current.'% '.$end.'%';
                $current = $end;
            }
            $pieBackground = $segments
                ? 'conic-gradient('.implode(', ', $segments).')'
                : 'conic-gradient(#e5e7eb 0 100%)';
        @endphp
        <div class="pie-wrap">
            <div class="pie" style="background: {{ $pieBackground }};">
                <div class="pie-center">
                    <strong>{{ number_format($totalPlatformPoints, 2) }}</strong>
                    <span class="muted">Total</span>
                </div>
            </div>
            <div class="legend">
                @forelse ($platformPoints as $index => $row)
                    @php
                        $percent = $totalPlatformPoints > 0
                            ? round(($row->total_points / $totalPlatformPoints) * 100)
                            : 0;
                    @endphp
                    <div class="legend-item">
                        <div class="legend-left">
                            <span class="legend-dot" style="background: {{ $colors[$index % count($colors)] }};"></span>
                            <span>{{ $row->platform }}</span>
                        </div>
                        <span>{{ number_format($row->total_points, 2) }} ({{ $percent }}%)</span>
                    </div>
                @empty
                    <span class="muted">Belum ada poin platform.</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Aktivitas vs Konversi</h3>
        <div class="split-card">
            <div class="split-row">
                <span>Poin Aktivitas</span>
                <span class="chip">{{ number_format($activityPoints, 2) }}</span>
            </div>
            <div class="split-row">
                <span>Poin Konversi</span>
                <span class="chip">{{ number_format($conversionPoints, 2) }}</span>
            </div>
            <div class="split-row">
                <strong>Total</strong>
                <strong>{{ number_format($activityPoints + $conversionPoints, 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Target Tahunan {{ $targetYear }}</h3>
        @php
            $closingMax = max($memberTargetClosing, $memberClosingAchieved);
            $closingTargetHeight = $closingMax > 0 ? round(($memberTargetClosing / $closingMax) * 100) : 0;
            $closingAchievedHeight = $closingMax > 0 ? round(($memberClosingAchieved / $closingMax) * 100) : 0;
            $leadsMax = max($memberTargetLeads, $memberLeadsAchieved);
            $leadsTargetHeight = $leadsMax > 0 ? round(($memberTargetLeads / $leadsMax) * 100) : 0;
            $leadsAchievedHeight = $leadsMax > 0 ? round(($memberLeadsAchieved / $leadsMax) * 100) : 0;
        @endphp
        <div class="target-metrics">
            <div class="muted">Closing</div>
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $closingTargetHeight }}%;"></div>
                        <div class="bar achieved" style="height: {{ $closingAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($memberTargetClosing, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($memberClosingAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="muted">{{ $memberClosingPercent }}%</div>
            </div>
            <div class="muted">Leads</div>
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $leadsTargetHeight }}%;"></div>
                        <div class="bar leads" style="height: {{ $leadsAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($memberTargetLeads, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($memberLeadsAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="muted">{{ $memberLeadsPercent }}%</div>
            </div>
        </div>
    </div>
</div>
