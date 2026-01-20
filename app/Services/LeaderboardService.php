<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public static function build(?int $limit = null): Collection
    {
        $staffTotals = ActivityLog::query()
            ->selectRaw('team_id, user_id, SUM(computed_points) as points')
            ->where('status', 'Verified')
            ->groupBy('team_id', 'user_id')
            ->unionAll(
                Conversion::query()
                    ->selectRaw('team_id, user_id, SUM(computed_points) as points')
                    ->where('status', 'Verified')
                    ->groupBy('team_id', 'user_id')
            );

        $staffPoints = DB::query()
            ->fromSub($staffTotals, 'totals')
            ->join('users', 'users.id', '=', 'totals.user_id')
            ->where('users.role', 'staff')
            ->selectRaw('totals.team_id, totals.user_id, users.name, SUM(points) as total_points')
            ->groupBy('totals.team_id', 'totals.user_id', 'users.name')
            ->get();

        $staffSummary = [];
        foreach ($staffPoints as $row) {
            $teamId = (int) $row->team_id;
            $points = (float) $row->total_points;

            if (! isset($staffSummary[$teamId])) {
                $staffSummary[$teamId] = [
                    'staff_total' => 0.0,
                    'top_name' => null,
                    'top_points' => 0.0,
                ];
            }

            $staffSummary[$teamId]['staff_total'] += $points;

            if ($points > $staffSummary[$teamId]['top_points']) {
                $staffSummary[$teamId]['top_points'] = $points;
                $staffSummary[$teamId]['top_name'] = $row->name;
            }
        }

        $teams = Team::query()
            ->select('teams.*')
            ->selectSub(
                ActivityLog::query()
                    ->selectRaw('COALESCE(SUM(computed_points), 0)')
                    ->whereColumn('team_id', 'teams.id')
                    ->where('status', 'Verified'),
                'activity_points'
            )
            ->selectSub(
                Conversion::query()
                    ->selectRaw('COALESCE(SUM(computed_points), 0)')
                    ->whereColumn('team_id', 'teams.id')
                    ->where('status', 'Verified'),
                'conversion_points'
            )
            ->get()
            ->map(function (Team $team) use ($staffSummary) {
                $summary = $staffSummary[$team->id] ?? null;

                $team->activity_points = (float) $team->activity_points;
                $team->conversion_points = (float) $team->conversion_points;
                $team->staff_points_total = $summary['staff_total'] ?? 0.0;
                $team->top_staff_name = $summary['top_name'] ?? null;
                $team->top_staff_points = $summary['top_points'] ?? 0.0;
                $team->total_points = $team->activity_points + $team->conversion_points;

                return $team;
            })
            ->sortByDesc('total_points')
            ->values();

        if ($limit !== null) {
            return $teams->take($limit);
        }

        return $teams;
    }
}
