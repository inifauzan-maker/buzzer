<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reminders:teams', function () {
    $count = app(\App\Services\ReminderService::class)->sendTeamReminders();

    $this->info('Reminder terkirim: '.$count);
})->purpose('Kirim reminder untuk tim yang tidak aktif');
