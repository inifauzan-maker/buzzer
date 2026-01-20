<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaderboard = LeaderboardService::build();

        return view('leaderboard', [
            'leaderboard' => $leaderboard,
        ]);
    }
}
