<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PointSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:superadmin')->group(function () {
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::post('/teams/members', [TeamController::class, 'storeMember'])->name('teams.members.store');
        Route::get('/settings/points', [PointSettingController::class, 'index'])->name('settings.points');
        Route::post('/settings/points', [PointSettingController::class, 'update'])->name('settings.points.update');
    });

    Route::middleware('role:superadmin,leader,staff')->group(function () {
        Route::get('/activities', [ActivityLogController::class, 'index'])->name('activities.index');
        Route::get('/activities/create', [ActivityLogController::class, 'create'])->name('activities.create');
        Route::post('/activities', [ActivityLogController::class, 'store'])->name('activities.store');

        Route::get('/conversions', [ConversionController::class, 'index'])->name('conversions.index');
        Route::get('/conversions/create', [ConversionController::class, 'create'])->name('conversions.create');
        Route::post('/conversions', [ConversionController::class, 'store'])->name('conversions.store');
    });

    Route::middleware('role:superadmin,leader')->group(function () {
        Route::post('/activities/{activity}/verify', [ActivityLogController::class, 'verify'])->name('activities.verify');
        Route::post('/activities/{activity}/reject', [ActivityLogController::class, 'reject'])->name('activities.reject');
        Route::post('/conversions/{conversion}/verify', [ConversionController::class, 'verify'])->name('conversions.verify');
        Route::post('/conversions/{conversion}/reject', [ConversionController::class, 'reject'])->name('conversions.reject');
    });
});
