<?php

namespace App\Http\Controllers;

use App\Models\AdsCampaign;
use App\Models\AdsMetric;
use App\Models\User;
use App\Services\SystemActivityLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdsController extends Controller
{
    private const PLATFORMS = [
        'Meta Ads',
        'Google Ads',
        'TikTok Ads',
        'Paid Promote',
        'Influencer',
    ];

    private const OBJECTIVES = [
        'Awareness',
        'Traffic',
        'Conversion',
    ];

    private const STATUSES = [
        'Draft',
        'Aktif',
        'Dihentikan',
        'Berakhir',
    ];

    private const PIC_ROLES = [
        'superadmin',
        'leader',
        'admin',
        'campaign_planner',
        'ads_specialist',
        'analyst',
        'management',
    ];

    private const PLAN_ROLES = ['superadmin', 'leader', 'admin', 'campaign_planner'];
    private const MONITOR_ROLES = ['superadmin', 'leader', 'admin', 'ads_specialist'];
    private const REPORT_ROLES = ['superadmin', 'leader', 'admin', 'analyst', 'management'];

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $this->filtersFromRequest($request);

        $campaignOptions = AdsCampaign::with('pic')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $campaigns = $this->applyCampaignFilters(
            AdsCampaign::with('pic')->orderByDesc('start_date')->orderByDesc('id'),
            $filters
        )->get();

        $metricsQuery = $this->applyMetricFilters(
            AdsMetric::with(['campaign', 'pic']),
            $filters
        );

        $metrics = (clone $metricsQuery)
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $pics = User::whereIn('role', self::PIC_ROLES)
            ->orderBy('name')
            ->get();

        $summary = (clone $metricsQuery)
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(impressions), 0) as total_impressions')
            ->selectRaw('COALESCE(SUM(reach), 0) as total_reach')
            ->selectRaw('COALESCE(SUM(leads_count), 0) as total_leads')
            ->selectRaw('COALESCE(SUM(closing_count), 0) as total_closing')
            ->first();

        $platformSummary = (clone $metricsQuery)
            ->selectRaw('ads_campaigns.platform as platform')
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(reach), 0) as total_reach')
            ->selectRaw('COALESCE(SUM(leads_count), 0) as total_leads')
            ->selectRaw('COALESCE(SUM(closing_count), 0) as total_closing')
            ->join('ads_campaigns', 'ads_campaigns.id', '=', 'ads_metrics.ads_campaign_id')
            ->groupBy('ads_campaigns.platform')
            ->orderByDesc('total_cost')
            ->get();

        $kpiByPlatform = $this->applyCampaignFilters(
            AdsCampaign::query(),
            $filters
        )
            ->selectRaw('platform')
            ->selectRaw('COALESCE(SUM(kpi_leads), 0) as kpi_leads')
            ->selectRaw('COALESCE(SUM(kpi_closing), 0) as kpi_closing')
            ->selectRaw('COALESCE(SUM(kpi_reach), 0) as kpi_reach')
            ->groupBy('platform')
            ->get()
            ->keyBy('platform');

        $actualByPlatform = $platformSummary->keyBy('platform');
        $platformPerformance = collect(array_unique($kpiByPlatform->keys()->merge($actualByPlatform->keys())->all()))
            ->map(function ($platform) use ($kpiByPlatform, $actualByPlatform) {
                $kpi = $kpiByPlatform->get($platform);
                $actual = $actualByPlatform->get($platform);

                return (object) [
                    'platform' => $platform,
                    'kpi_leads' => (int) ($kpi->kpi_leads ?? 0),
                    'kpi_closing' => (int) ($kpi->kpi_closing ?? 0),
                    'kpi_reach' => (int) ($kpi->kpi_reach ?? 0),
                    'actual_leads' => (int) ($actual->total_leads ?? 0),
                    'actual_closing' => (int) ($actual->total_closing ?? 0),
                    'actual_reach' => (int) ($actual->total_reach ?? 0),
                ];
            });

        [$trendSeries, $trendMax] = $this->buildTrend($metricsQuery, $filters);
        $trendChange = $this->buildTrendChange($trendSeries);

        $role = $user?->role ?? '';

        return view('ads.index', [
            'campaigns' => $campaigns,
            'campaignOptions' => $campaignOptions,
            'metrics' => $metrics,
            'pics' => $pics,
            'summary' => $summary,
            'platformSummary' => $platformSummary,
            'platformPerformance' => $platformPerformance,
            'platforms' => self::PLATFORMS,
            'objectives' => self::OBJECTIVES,
            'statuses' => self::STATUSES,
            'canPlan' => in_array($role, self::PLAN_ROLES, true),
            'canMonitor' => in_array($role, self::MONITOR_ROLES, true),
            'canReport' => in_array($role, self::REPORT_ROLES, true),
            'filters' => $filters,
            'trendSeries' => $trendSeries,
            'trendMax' => $trendMax,
            'trendChange' => $trendChange,
        ]);
    }

    public function storeCampaign(Request $request)
    {
        $role = $request->user()?->role ?? '';
        if (! in_array($role, self::PLAN_ROLES, true)) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'platform' => ['required', Rule::in(self::PLATFORMS)],
            'objective' => ['required', Rule::in(self::OBJECTIVES)],
            'brief' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'budget_plan' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'kpi_leads' => 'nullable|integer|min:0',
            'kpi_closing' => 'nullable|integer|min:0',
            'kpi_reach' => 'nullable|integer|min:0',
            'pic_id' => 'nullable|exists:users,id',
        ]);

        $data['status'] = $data['status'] ?? 'Draft';
        $data['budget_plan'] = $data['budget_plan'] ?? 0;
        $data['kpi_leads'] = $data['kpi_leads'] ?? 0;
        $data['kpi_closing'] = $data['kpi_closing'] ?? 0;
        $data['kpi_reach'] = $data['kpi_reach'] ?? 0;
        $data['created_by'] = $request->user()->id;

        $campaign = AdsCampaign::create($data);

        SystemActivityLogger::log($request->user(), 'Menambahkan kampanye ads "'.$campaign->name.'".');

        return redirect()
            ->route('ads.index')
            ->with('status', 'Kampanye berhasil ditambahkan.');
    }

    public function storeMetric(Request $request)
    {
        $role = $request->user()?->role ?? '';
        if (! in_array($role, self::MONITOR_ROLES, true)) {
            abort(403);
        }

        $data = $request->validate([
            'ads_campaign_id' => 'required|exists:ads_campaigns,id',
            'report_date' => 'nullable|date',
            'pic_id' => 'nullable|exists:users,id',
            'cost' => 'nullable|numeric|min:0',
            'product' => 'nullable|string|max:160',
            'content_url' => 'nullable|url',
            'impressions' => 'nullable|integer|min:0',
            'reach' => 'nullable|integer|min:0',
            'clicks_wa' => 'nullable|integer|min:0',
            'leads_count' => 'nullable|integer|min:0',
            'closing_count' => 'nullable|integer|min:0',
            'views_3s' => 'nullable|integer|min:0',
            'views_50s' => 'nullable|integer|min:0',
            'reactions' => 'nullable|integer|min:0',
            'link_clicks' => 'nullable|integer|min:0',
            'saves' => 'nullable|integer|min:0',
            'shares' => 'nullable|integer|min:0',
            'profile_visits' => 'nullable|integer|min:0',
            'follows' => 'nullable|integer|min:0',
            'gender_male' => 'nullable|numeric|min:0|max:100',
            'gender_female' => 'nullable|numeric|min:0|max:100',
            'age_18_24' => 'nullable|numeric|min:0|max:100',
            'age_25_34' => 'nullable|numeric|min:0|max:100',
            'age_35_44' => 'nullable|numeric|min:0|max:100',
            'age_45_54' => 'nullable|numeric|min:0|max:100',
            'age_55_64' => 'nullable|numeric|min:0|max:100',
            'age_65_plus' => 'nullable|numeric|min:0|max:100',
        ]);

        $campaign = AdsCampaign::find($data['ads_campaign_id']);
        if (! $data['pic_id']) {
            $data['pic_id'] = $campaign?->pic_id;
        }
        $data['report_date'] = $data['report_date'] ?? now()->toDateString();
        $data['cost'] = $data['cost'] ?? 0;

        $data['top_locations'] = $this->buildLocations(
            $request->input('top_location_name', []),
            $request->input('top_location_percent', [])
        );

        $metric = AdsMetric::create($data);

        SystemActivityLogger::log(
            $request->user(),
            'Menambahkan laporan ads untuk kampanye "'.$campaign?->name.'".'
        );

        return redirect()
            ->route('ads.index')
            ->with('status', 'Data monitoring berhasil disimpan.');
    }

    public function exportCsv(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $metrics = $this->applyMetricFilters(
            AdsMetric::with(['campaign', 'pic']),
            $filters
        )
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'ads-report-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Tanggal',
            'Campaign',
            'Platform',
            'PIC',
            'Biaya',
            'Tayangan',
            'Jangkauan',
            'Klik WA',
            'Leads',
            'Closing',
            'Pemutaran 3 Detik',
            'Pemutaran 50 Detik',
            'Suka & Tanggapan',
            'Klik Tautan',
            'Simpan',
            'Bagikan',
            'Kunjungan Profil',
            'Mengikuti',
        ];

        return response()->streamDownload(function () use ($metrics, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($metrics as $metric) {
                fputcsv($handle, [
                    optional($metric->report_date)->format('Y-m-d'),
                    $metric->campaign?->name ?? '',
                    $metric->campaign?->platform ?? '',
                    $metric->pic?->name ?? '',
                    $metric->cost,
                    $metric->impressions,
                    $metric->reach,
                    $metric->clicks_wa,
                    $metric->leads_count,
                    $metric->closing_count,
                    $metric->views_3s,
                    $metric->views_50s,
                    $metric->reactions,
                    $metric->link_clicks,
                    $metric->saves,
                    $metric->shares,
                    $metric->profile_visits,
                    $metric->follows,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPrint(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $metrics = $this->applyMetricFilters(
            AdsMetric::with(['campaign', 'pic']),
            $filters
        )
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get();

        return view('ads.report-print', [
            'metrics' => $metrics,
            'filters' => $filters,
        ]);
    }

    private function buildLocations(array $names, array $percents): array
    {
        $locations = [];
        $max = max(count($names), count($percents));

        for ($i = 0; $i < $max; $i++) {
            $name = trim($names[$i] ?? '');
            $percent = trim((string) ($percents[$i] ?? ''));

            if ($name === '' && $percent === '') {
                continue;
            }

            $locations[] = [
                'name' => $name,
                'percent' => $percent === '' ? null : (float) $percent,
            ];
        }

        return $locations;
    }

    private function filtersFromRequest(Request $request): array
    {
        $period = $request->input('period');
        $period = in_array($period, ['daily', 'weekly', 'monthly'], true) ? $period : 'daily';

        return [
            'campaign_id' => $request->integer('campaign_id') ?: null,
            'platform' => $request->string('platform')->toString() ?: null,
            'pic_id' => $request->integer('pic_id') ?: null,
            'start_date' => $request->input('start_date') ?: null,
            'end_date' => $request->input('end_date') ?: null,
            'period' => $period,
        ];
    }

    private function applyMetricFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['campaign_id'])) {
            $query->where('ads_campaign_id', $filters['campaign_id']);
        }

        if (! empty($filters['pic_id'])) {
            $query->where('pic_id', $filters['pic_id']);
        }

        if (! empty($filters['platform'])) {
            $query->whereHas('campaign', function ($q) use ($filters) {
                $q->where('platform', $filters['platform']);
            });
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('report_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('report_date', '<=', $filters['end_date']);
        }

        return $query;
    }

    private function applyCampaignFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['campaign_id'])) {
            $query->where('id', $filters['campaign_id']);
        }

        if (! empty($filters['pic_id'])) {
            $query->where('pic_id', $filters['pic_id']);
        }

        if (! empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        return $query;
    }

    private function buildTrend(Builder $metricsQuery, array $filters): array
    {
        $start = $filters['start_date']
            ? Carbon::parse($filters['start_date'])
            : now()->subDays(29);
        $end = $filters['end_date']
            ? Carbon::parse($filters['end_date'])
            : now();

        $metrics = (clone $metricsQuery)
            ->whereNotNull('report_date')
            ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $grouped = [];
        foreach ($metrics as $metric) {
            $date = Carbon::parse($metric->report_date);
            $key = match ($filters['period']) {
                'weekly' => $date->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                'monthly' => $date->format('Y-m-01'),
                default => $date->format('Y-m-d'),
            };
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'label' => $this->formatTrendLabel($key, $filters['period']),
                    'leads' => 0,
                    'closing' => 0,
                ];
            }
            $grouped[$key]['leads'] += (int) $metric->leads_count;
            $grouped[$key]['closing'] += (int) $metric->closing_count;
        }

        ksort($grouped);

        $series = array_values($grouped);
        $max = 1;
        foreach ($series as $item) {
            $max = max($max, $item['leads'], $item['closing']);
        }

        return [$series, $max];
    }

    private function formatTrendLabel(string $key, string $period): string
    {
        $date = Carbon::parse($key);
        return match ($period) {
            'weekly' => 'Minggu '. $date->format('d M'),
            'monthly' => $date->translatedFormat('M Y'),
            default => $date->translatedFormat('d M'),
        };
    }

    private function buildTrendChange(array $series): array
    {
        $count = count($series);
        if ($count < 2) {
            return [
                'lead_change' => null,
                'closing_change' => null,
            ];
        }

        $last = $series[$count - 1];
        $prev = $series[$count - 2];

        $leadChange = $this->percentChange($prev['leads'], $last['leads']);
        $closingChange = $this->percentChange($prev['closing'], $last['closing']);

        return [
            'lead_change' => $leadChange,
            'closing_change' => $closingChange,
        ];
    }

    private function percentChange(int $previous, int $current): array
    {
        $diff = $current - $previous;
        $percent = $previous > 0 ? ($diff / $previous) * 100 : null;

        return [
            'diff' => $diff,
            'percent' => $percent,
            'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
        ];
    }
}
