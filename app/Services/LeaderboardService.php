<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Team;
use Illuminate\Support\Collection;

class LeaderboardService
{
    public static function build(?int $limit = null): Collection
    {
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
            ->map(function (Team $team) {
                $team->activity_points = (float) $team->activity_points;
                $team->conversion_points = (float) $team->conversion_points;
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
