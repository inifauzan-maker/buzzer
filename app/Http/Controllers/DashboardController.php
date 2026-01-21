<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use App\Models\TeamMemberTarget;
use App\Models\TeamTarget;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $activityBase = ActivityLog::query();
        $conversionBase = Conversion::query();
        $teamBase = Team::query();
        $userBase = User::query();

        if ($user->role === 'staff') {
            $activityBase->where('user_id', $user->id);
            $conversionBase->where('user_id', $user->id);
            $teamBase->where('id', $user->team_id);
            $userBase->where('id', $user->id);
        } elseif ($user->role === 'leader') {
            $activityBase->where('team_id', $user->team_id);
            $conversionBase->where('team_id', $user->team_id);
            $teamBase->where('id', $user->team_id);
            $userBase->where('team_id', $user->team_id);
        }

        $totalTeams = $teamBase->count();
        $totalUsers = $userBase->count();

        $pendingActivities = (clone $activityBase)->where('status', 'Pending')->count();
        $pendingConversions = (clone $conversionBase)->where('status', 'Pending')->count();
        $verifiedActivities = (clone $activityBase)->where('status', 'Verified')->count();
        $verifiedConversions = (clone $conversionBase)->where('status', 'Verified')->count();
        $totalActivities = (clone $activityBase)->count();
        $totalConversions = (clone $conversionBase)->count();

        $activityPoints = (clone $activityBase)->where('status', 'Verified')->sum('computed_points');
        $conversionPoints = (clone $conversionBase)->where('status', 'Verified')->sum('computed_points');
        $totalPoints = $activityPoints + $conversionPoints;

        $activityCompletion = $totalActivities > 0
            ? round(($verifiedActivities / $totalActivities) * 100)
            : 0;
        $conversionCompletion = $totalConversions > 0
            ? round(($verifiedConversions / $totalConversions) * 100)
            : 0;
        $pendingTotal = $pendingActivities + $pendingConversions;
        $totalItems = $totalActivities + $totalConversions;
        $pendingPercent = $totalItems > 0
            ? round(($pendingTotal / $totalItems) * 100)
            : 0;

        $pointTarget = 1000;
        $pointPercent = $pointTarget > 0
            ? min(100, round(($totalPoints / $pointTarget) * 100))
            : 0;

        $targetYear = now()->year;
        $targetLabel = 'Target Keseluruhan';
        $targetClosing = 0;
        $targetLeads = 0;

        if ($user->role === 'staff') {
            $targetLabel = 'Target Saya';
            $memberTarget = TeamMemberTarget::query()
                ->where('user_id', $user->id)
                ->where('year', $targetYear)
                ->where('month', 0)
                ->first();
            $targetClosing = (int) ($memberTarget?->target_closing ?? 0);
            $targetLeads = (int) ($memberTarget?->target_leads ?? 0);
        } elseif ($user->role === 'leader') {
            $targetLabel = 'Target Tim';
            $teamTarget = TeamTarget::query()
                ->where('team_id', $user->team_id)
                ->where('year', $targetYear)
                ->where('month', 0)
                ->first();
            $targetClosing = (int) ($teamTarget?->target_closing ?? 0);
            $targetLeads = (int) ($teamTarget?->target_leads ?? 0);
        } else {
            $targetClosing = (int) TeamTarget::query()
                ->where('year', $targetYear)
                ->where('month', 0)
                ->sum('target_closing');
            $targetLeads = (int) TeamTarget::query()
                ->where('year', $targetYear)
                ->where('month', 0)
                ->sum('target_leads');
        }

        $targetClosingAchieved = (clone $conversionBase)
            ->where('status', 'Verified')
            ->where('type', 'Closing')
            ->whereYear('created_at', $targetYear)
            ->sum('amount');

        $targetLeadsAchieved = (clone $conversionBase)
            ->where('status', 'Verified')
            ->where('type', 'Lead')
            ->whereYear('created_at', $targetYear)
            ->sum('amount');

        $targetClosingPercent = $targetClosing > 0
            ? min(100, (int) round(($targetClosingAchieved / $targetClosing) * 100))
            : 0;
        $targetLeadsPercent = $targetLeads > 0
            ? min(100, (int) round(($targetLeadsAchieved / $targetLeads) * 100))
            : 0;
        $closingMax = max($targetClosing, $targetClosingAchieved);
        $closingTargetHeight = $closingMax > 0
            ? (int) round(($targetClosing / $closingMax) * 100)
            : 0;
        $closingAchievedHeight = $closingMax > 0
            ? (int) round(($targetClosingAchieved / $closingMax) * 100)
            : 0;
        $leadsMax = max($targetLeads, $targetLeadsAchieved);
        $leadsTargetHeight = $leadsMax > 0
            ? (int) round(($targetLeads / $leadsMax) * 100)
            : 0;
        $leadsAchievedHeight = $leadsMax > 0
            ? (int) round(($targetLeadsAchieved / $leadsMax) * 100)
            : 0;

        $leadSeries = [];
        $leadMax = 0;
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = (clone $conversionBase)
                ->where('type', 'Lead')
                ->whereDate('created_at', $date->toDateString())
                ->count();
            $leadSeries[] = [
                'date' => $date->format('d M'),
                'count' => $count,
            ];
            $leadMax = max($leadMax, $count);
        }
        $leadSeries = array_map(function (array $lead) use ($leadMax) {
            $height = $leadMax > 0 ? (int) round(($lead['count'] / $leadMax) * 100) : 0;
            $lead['height'] = max($height, 6);

            return $lead;
        }, $leadSeries);
        $leadDailyTotal = array_sum(array_column($leadSeries, 'count'));

        $closingTotal = (clone $conversionBase)
            ->where('type', 'Closing')
            ->where('status', 'Verified')
            ->sum('amount');

        $leadTotal = (clone $conversionBase)
            ->where('type', 'Lead')
            ->where('status', 'Verified')
            ->sum('amount');

        $cutoff = Carbon::today()->subDays(2);
        $inactiveTeams = $teamBase
            ->select('teams.*')
            ->selectSub(
                ActivityLog::query()
                    ->selectRaw('MAX(post_date)')
                    ->whereColumn('team_id', 'teams.id'),
                'last_post_date'
            )
            ->get()
            ->filter(function (Team $team) use ($cutoff) {
                if (! $team->last_post_date) {
                    return true;
                }

                return Carbon::parse($team->last_post_date)->lt($cutoff);
            })
            ->take(4);

        $teamMemberPoints = collect();

        if ($user->role === 'leader' && $user->team_id) {
            $activityByUser = ActivityLog::query()
                ->selectRaw('user_id, COALESCE(SUM(computed_points), 0) as activity_points')
                ->where('status', 'Verified')
                ->groupBy('user_id');

            $conversionByUser = Conversion::query()
                ->selectRaw('user_id, COALESCE(SUM(computed_points), 0) as conversion_points')
                ->where('status', 'Verified')
                ->groupBy('user_id');

            $teamMemberPoints = User::query()
                ->where('team_id', $user->team_id)
                ->leftJoinSub($activityByUser, 'activity_points', 'activity_points.user_id', '=', 'users.id')
                ->leftJoinSub($conversionByUser, 'conversion_points', 'conversion_points.user_id', '=', 'users.id')
                ->select('users.*')
                ->selectRaw('COALESCE(activity_points.activity_points, 0) as activity_points')
                ->selectRaw('COALESCE(conversion_points.conversion_points, 0) as conversion_points')
                ->orderBy('role')
                ->orderBy('name')
                ->get()
                ->map(function (User $member) {
                    $member->activity_points = (float) $member->activity_points;
                    $member->conversion_points = (float) $member->conversion_points;
                    $member->total_points = $member->activity_points + $member->conversion_points;

                    return $member;
                });
        }

        $heatmap = $this->buildHeatmap(
            (clone $activityBase)->where('status', 'Verified'),
            Carbon::today()->subDays(364),
            Carbon::today()
        );

        return view('dashboard', [
            'totalTeams' => $totalTeams,
            'totalUsers' => $totalUsers,
            'pendingActivities' => $pendingActivities,
            'pendingConversions' => $pendingConversions,
            'verifiedActivities' => $verifiedActivities,
            'verifiedConversions' => $verifiedConversions,
            'totalActivities' => $totalActivities,
            'totalConversions' => $totalConversions,
            'activityPoints' => $activityPoints,
            'conversionPoints' => $conversionPoints,
            'totalPoints' => $totalPoints,
            'activityCompletion' => $activityCompletion,
            'conversionCompletion' => $conversionCompletion,
            'pendingTotal' => $pendingTotal,
            'pendingPercent' => $pendingPercent,
            'pointTarget' => $pointTarget,
            'pointPercent' => $pointPercent,
            'leadSeries' => $leadSeries,
            'leadMax' => $leadMax,
            'leadDailyTotal' => $leadDailyTotal,
            'closingTotal' => $closingTotal,
            'leadTotal' => $leadTotal,
            'inactiveTeams' => $inactiveTeams,
            'teamMemberPoints' => $teamMemberPoints,
            'heatmap' => $heatmap,
            'targetLabel' => $targetLabel,
            'targetYear' => $targetYear,
            'targetClosing' => $targetClosing,
            'targetLeads' => $targetLeads,
            'targetClosingAchieved' => $targetClosingAchieved,
            'targetLeadsAchieved' => $targetLeadsAchieved,
            'targetClosingPercent' => $targetClosingPercent,
            'targetLeadsPercent' => $targetLeadsPercent,
            'closingTargetHeight' => $closingTargetHeight,
            'closingAchievedHeight' => $closingAchievedHeight,
            'leadsTargetHeight' => $leadsTargetHeight,
            'leadsAchievedHeight' => $leadsAchievedHeight,
        ]);
    }

    private function buildHeatmap(Builder $query, Carbon $startDate, Carbon $endDate): array
    {
        $counts = $query
            ->selectRaw('post_date, COUNT(*) as total')
            ->whereBetween('post_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('post_date')
            ->pluck('total', 'post_date')
            ->map(fn ($value) => (int) $value)
            ->all();

        $maxCount = 0;
        if (! empty($counts)) {
            $maxCount = max(array_values($counts));
        }

        $totalCount = array_sum($counts);
        $weeks = [];
        $monthLabels = [];

        $cursor = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endCursor = $endDate->copy()->endOfWeek(Carbon::MONDAY);
        $weekIndex = 0;

        while ($cursor <= $endCursor) {
            $week = [];
            $label = null;

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->toDateString();
                $inRange = $cursor->between($startDate, $endDate, true);
                $count = $inRange ? ($counts[$dateKey] ?? 0) : null;
                $level = $this->heatmapLevel($count, $maxCount, $inRange);

                if ($inRange && $cursor->day === 1) {
                    $label = $cursor->format('M');
                }

                $week[] = [
                    'date' => $dateKey,
                    'count' => $count,
                    'level' => $level,
                    'in_range' => $inRange,
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;

            if ($label) {
                $monthLabels[$weekIndex] = $label;
            }

            $weekIndex++;
        }

        return [
            'weeks' => $weeks,
            'month_labels' => $monthLabels,
            'total' => $totalCount,
            'max' => $maxCount,
        ];
    }

    private function heatmapLevel(?int $count, int $maxCount, bool $inRange): int
    {
        if (! $inRange) {
            return -1;
        }

        if (! $count || $maxCount === 0) {
            return 0;
        }

        $ratio = $count / $maxCount;

        if ($ratio <= 0.25) {
            return 1;
        }

        if ($ratio <= 0.5) {
            return 2;
        }

        if ($ratio <= 0.75) {
            return 3;
        }

        return 4;
    }

}
