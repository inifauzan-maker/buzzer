<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use App\Models\User;
use App\Services\LeaderboardService;
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

        $leaderboard = LeaderboardService::build(5);
        $heatmap = $this->buildHeatmap(
            (clone $activityBase)->where('status', 'Verified'),
            Carbon::today()->subDays(364),
            Carbon::today()
        );

        $topActivityUsers = $this->buildTopUsersByActivity(clone $activityBase);
        $topClosingUsers = $this->buildTopUsersByClosing(clone $conversionBase);

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
            'closingTotal' => $closingTotal,
            'leadTotal' => $leadTotal,
            'inactiveTeams' => $inactiveTeams,
            'teamMemberPoints' => $teamMemberPoints,
            'leaderboard' => $leaderboard,
            'heatmap' => $heatmap,
            'topActivityUsers' => $topActivityUsers,
            'topClosingUsers' => $topClosingUsers,
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

    private function buildTopUsersByActivity(Builder $baseQuery)
    {
        $activityTotals = $baseQuery
            ->where('status', 'Verified')
            ->selectRaw('user_id, COUNT(*) as total_activities')
            ->groupBy('user_id');

        return User::query()
            ->joinSub($activityTotals, 'activity_totals', 'activity_totals.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'activity_totals.total_activities')
            ->orderByDesc('activity_totals.total_activities')
            ->limit(5)
            ->get();
    }

    private function buildTopUsersByClosing(Builder $baseQuery)
    {
        $closingTotals = $baseQuery
            ->where('status', 'Verified')
            ->where('type', 'Closing')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total_closing')
            ->groupBy('user_id');

        return User::query()
            ->joinSub($closingTotals, 'closing_totals', 'closing_totals.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'closing_totals.total_closing')
            ->orderByDesc('closing_totals.total_closing')
            ->limit(5)
            ->get();
    }
}
