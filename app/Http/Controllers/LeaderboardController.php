<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
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

        $leaderboardLeaders = (clone $baseQuery)
            ->where('users.role', 'leader')
            ->orderByDesc(DB::raw('total_points'))
            ->get();

        $leaderboardStaff = (clone $baseQuery)
            ->where('users.role', 'staff')
            ->orderByDesc(DB::raw('total_points'))
            ->get();

        return view('leaderboard', [
            'leaderboardLeaders' => $leaderboardLeaders,
            'leaderboardStaff' => $leaderboardStaff,
        ]);
    }
}
