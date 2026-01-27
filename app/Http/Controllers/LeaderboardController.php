<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $activityTotals = ActivityLog::query()
            ->selectRaw('user_id, COALESCE(SUM(computed_points), 0) as activity_points')
            ->where('status', 'Verified')
            ->groupBy('user_id');

        $conversionTotals = Conversion::query()
            ->selectRaw('user_id, COALESCE(SUM(computed_points), 0) as conversion_points')
            ->where('status', 'Verified')
            ->groupBy('user_id');

        $baseQuery = User::query()
            ->leftJoinSub($activityTotals, 'activity_points', 'activity_points.user_id', '=', 'users.id')
            ->leftJoinSub($conversionTotals, 'conversion_points', 'conversion_points.user_id', '=', 'users.id')
            ->leftJoin('teams', 'teams.id', '=', 'users.team_id')
            ->select(
                'users.id',
                'users.name',
                'users.role',
                'teams.team_name'
            )
            ->selectRaw('COALESCE(activity_points.activity_points, 0) as activity_points')
            ->selectRaw('COALESCE(conversion_points.conversion_points, 0) as conversion_points')
            ->selectRaw('(COALESCE(activity_points.activity_points, 0) + COALESCE(conversion_points.conversion_points, 0)) as total_points');

        $teams = Team::query()
            ->orderBy('team_name')
            ->get(['id', 'team_name']);

        $teamId = request()->query('team_id');
        $sort = request()->query('sort', 'total');

        $leaderboardStaff = (clone $baseQuery)
            ->where('users.role', 'staff')
            ->when($teamId, fn ($query) => $query->where('users.team_id', $teamId))
            ->when($sort === 'activity', fn ($query) => $query->orderByDesc(DB::raw('activity_points')))
            ->when($sort === 'conversion', fn ($query) => $query->orderByDesc(DB::raw('conversion_points')))
            ->when($sort === 'total', fn ($query) => $query->orderByDesc(DB::raw('total_points')))
            ->get();

        return view('leaderboard', [
            'leaderboardStaff' => $leaderboardStaff,
            'teams' => $teams,
            'selectedTeamId' => $teamId,
            'selectedSort' => $sort,
        ]);
    }
}
