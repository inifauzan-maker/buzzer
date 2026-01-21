<?php

namespace App\Http\Controllers;

use App\Models\SystemActivityLog;
use App\Services\SystemActivityLogger;
use Illuminate\Http\Request;

class SystemActivityLogController extends Controller
{
    public function index()
    {
        $logs = SystemActivityLog::query()
            ->latest()
            ->paginate(30);

        return view('activity-logs', [
            'logs' => $logs,
        ]);
    }

    public function clear(Request $request)
    {
        SystemActivityLog::query()->delete();

        SystemActivityLogger::log($request->user(), 'Membersihkan log aktivitas');

        return redirect()
            ->route('activity-logs.index')
            ->with('status', 'Log aktivitas berhasil dibersihkan.');
    }
}
