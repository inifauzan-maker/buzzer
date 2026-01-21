<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\ActivityLog;
use App\Services\PointCalculator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reminders:teams', function () {
    $count = app(\App\Services\ReminderService::class)->sendTeamReminders();

    $this->info('Reminder terkirim: '.$count);
})->purpose('Kirim reminder untuk tim yang tidak aktif');

Artisan::command('points:recalculate-activities {--status=Verified}', function () {
    $status = $this->option('status') ?? 'Verified';
    $query = ActivityLog::query();

    if ($status !== 'all') {
        $query->where('status', $status);
    }

    $total = 0;
    $updated = 0;

    $query->orderBy('id')->chunkById(200, function ($activities) use (&$total, &$updated) {
        foreach ($activities as $activity) {
            $total++;
            $points = PointCalculator::activity($activity);
            if (abs((float) $activity->computed_points - $points) > 0.0001) {
                $activity->computed_points = $points;
                $activity->save();
                $updated++;
            }
        }
    });

    $this->info('Recalculate selesai. Total: '.$total.' | Updated: '.$updated);
})->purpose('Hitung ulang poin aktivitas berdasarkan ER rate');
