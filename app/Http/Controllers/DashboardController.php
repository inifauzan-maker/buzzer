<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Support\Carbon;

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
            'inactiveTeams' => $inactiveTeams,
            'teamMemberPoints' => $teamMemberPoints,
            'leaderboard' => $leaderboard,
        ]);
    }
}
