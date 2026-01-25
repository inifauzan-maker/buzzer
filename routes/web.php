<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointSettingController;
use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemActivityLogController;
use App\Http\Controllers\TeamTargetController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/pendaftaran', [\App\Http\Controllers\PublicRegistrationController::class, 'create'])->name('public.register');
Route::post('/pendaftaran', [\App\Http\Controllers\PublicRegistrationController::class, 'store'])->name('public.register.store');
Route::get('/pendaftaran/sekolah', [\App\Http\Controllers\PublicRegistrationController::class, 'schools'])
    ->name('public.register.schools');
Route::get('/pendaftaran/programs', [\App\Http\Controllers\PublicRegistrationController::class, 'programs'])
    ->name('public.register.programs');

Route::middleware('auth')->group(function () {
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profiles/{user}', [ProfileController::class, 'showUser'])->name('profile.view');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/social-accounts', [ProfileController::class, 'storeSocialAccount'])->name('profile.social.store');
    Route::patch('/profile/social-accounts/{socialAccount}', [ProfileController::class, 'updateSocialAccount'])->name('profile.social.update');
    Route::delete('/profile/social-accounts/{socialAccount}', [ProfileController::class, 'destroySocialAccount'])->name('profile.social.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::middleware('role:superadmin')->group(function () {
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::get('/data-siswa', [DataSiswaController::class, 'index'])->name('data-siswa.index');
        Route::post('/teams/members', [TeamController::class, 'storeMember'])->name('teams.members.store');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/targets/admin', [TeamTargetController::class, 'adminIndex'])->name('targets.admin');
        Route::get('/activity-logs', [SystemActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::post('/activity-logs/clear', [SystemActivityLogController::class, 'clear'])->name('activity-logs.clear');
        Route::get('/settings/points', [PointSettingController::class, 'index'])->name('settings.points');
        Route::post('/settings/points', [PointSettingController::class, 'update'])->name('settings.points.update');
    });

    Route::middleware('role:superadmin,leader,staff')->group(function () {
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    });

    Route::middleware('role:superadmin,admin,campaign_planner,ads_specialist,analyst,management')->group(function () {
        Route::get('/ads', [AdsController::class, 'index'])->name('ads.index');
        Route::post('/ads/campaigns', [AdsController::class, 'storeCampaign'])->name('ads.campaigns.store');
        Route::post('/ads/metrics', [AdsController::class, 'storeMetric'])->name('ads.metrics.store');
        Route::get('/ads/export/csv', [AdsController::class, 'exportCsv'])->name('ads.export.csv');
        Route::get('/ads/export/pdf', [AdsController::class, 'exportPrint'])->name('ads.export.pdf');
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    });

    Route::middleware('role:superadmin,leader,staff,guest')->group(function () {
        Route::get('/activities', [ActivityLogController::class, 'index'])->name('activities.index');
        Route::get('/conversions', [ConversionController::class, 'index'])->name('conversions.index');
    });

    Route::middleware('role:superadmin,leader,staff')->group(function () {
        Route::get('/activities/create', [ActivityLogController::class, 'create'])->name('activities.create');
        Route::post('/activities', [ActivityLogController::class, 'store'])->name('activities.store');
        Route::get('/activities/{activity}/edit', [ActivityLogController::class, 'edit'])->name('activities.edit');
        Route::patch('/activities/{activity}', [ActivityLogController::class, 'update'])->name('activities.update');

        Route::get('/conversions/create', [ConversionController::class, 'create'])->name('conversions.create');
        Route::post('/conversions', [ConversionController::class, 'store'])->name('conversions.store');
    });

    Route::middleware('role:superadmin,leader')->group(function () {
        Route::post('/activities/{activity}/verify', [ActivityLogController::class, 'verify'])->name('activities.verify');
        Route::post('/activities/{activity}/reject', [ActivityLogController::class, 'reject'])->name('activities.reject');
        Route::post('/conversions/{conversion}/verify', [ConversionController::class, 'verify'])->name('conversions.verify');
        Route::post('/conversions/{conversion}/reject', [ConversionController::class, 'reject'])->name('conversions.reject');
    });

    Route::middleware('role:leader,staff')->group(function () {
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{thread}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');
        Route::post('/chat/{thread}/messages', [ChatController::class, 'storeMessage'])->name('chat.messages.store');
    });

    Route::middleware('role:leader')->group(function () {
        Route::get('/targets', [TeamTargetController::class, 'index'])->name('targets.index');
        Route::post('/targets', [TeamTargetController::class, 'store'])->name('targets.store');
        Route::post('/targets/members', [TeamTargetController::class, 'storeMembers'])->name('targets.members.store');
    });
});
