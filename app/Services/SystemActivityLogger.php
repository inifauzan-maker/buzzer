<?php

namespace App\Services;

use App\Models\SystemActivityLog;
use App\Models\User;

class SystemActivityLogger
{
    public static function log(?User $user, string $activity): void
    {
        $request = request();

        SystemActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'role' => $user?->role,
            'activity' => $activity,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
