<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function sendTeamReminders(): int
    {
        $cutoff = now()->subDays(2)->startOfDay();

        $teams = Team::query()
            ->select('teams.*')
            ->selectSub(
                ActivityLog::query()
                    ->selectRaw('MAX(post_date)')
                    ->whereColumn('team_id', 'teams.id'),
                'last_post_date'
            )
            ->with('leader')
            ->get();

        $sent = 0;

        foreach ($teams as $team) {
            $lastPost = $team->last_post_date
                ? Carbon::parse($team->last_post_date)
                : null;

            $inactive = $lastPost === null || $lastPost->lt($cutoff);

            if (! $inactive) {
                continue;
            }

            if ($team->last_reminded_at && $team->last_reminded_at->isSameDay(now())) {
                continue;
            }

            $phone = $team->reminder_phone ?: $team->leader?->phone;

            if (! $phone) {
                Log::warning('Reminder dilewati karena nomor tidak tersedia.', [
                    'team_id' => $team->id,
                    'team_name' => $team->team_name,
                ]);
                continue;
            }

            $message = 'Reminder: Tim '.$team->team_name.' belum posting selama 2 hari terakhir.';

            Log::info('[REMINDER]', [
                'team_id' => $team->id,
                'team_name' => $team->team_name,
                'phone' => $phone,
                'message' => $message,
                'last_post_date' => $lastPost?->toDateString(),
            ]);

            $team->last_reminded_at = now();
            $team->save();
            $sent++;
        }

        return $sent;
    }
}
