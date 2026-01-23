<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\Conversion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $collection = Notification::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function (Notification $n) {
                return [
                    'title' => $n->title,
                    'message' => $n->message,
                    'status' => $n->read_at ? 'Dibaca' : 'Baru',
                    'created_at' => $n->created_at,
                ];
            });

        if ($user->role === 'superadmin') {
            $collection = $collection->merge(
                ActivityLog::where('status', 'Reviewed')
                    ->latest()
                    ->take(50)
                    ->get()
                    ->map(function (ActivityLog $log) {
                        return [
                            'title' => 'Aktivitas perlu keputusan',
                            'message' => 'Aktivitas '.$log->platform.' pada '.$log->post_date?->format('d M Y'),
                            'status' => 'Reviewed',
                            'created_at' => $log->created_at,
                        ];
                    })
            )->merge(
                Conversion::where('status', 'Reviewed')
                    ->latest()
                    ->take(50)
                    ->get()
                    ->map(function (Conversion $conv) {
                        return [
                            'title' => 'Konversi perlu keputusan',
                            'message' => ucfirst($conv->type).' sebesar '.$conv->amount,
                            'status' => 'Reviewed',
                            'created_at' => $conv->created_at,
                        ];
                    })
            );
        } elseif ($user->role === 'leader') {
            $collection = $collection->merge(
                ActivityLog::where('status', 'Pending')
                    ->where('team_id', $user->team_id)
                    ->latest()
                    ->take(50)
                    ->get()
                    ->map(function (ActivityLog $log) {
                        return [
                            'title' => 'Aktivitas menunggu verifikasi',
                            'message' => 'Aktivitas '.$log->platform.' pada '.$log->post_date?->format('d M Y'),
                            'status' => 'Pending',
                            'created_at' => $log->created_at,
                        ];
                    })
            )->merge(
                Conversion::where('status', 'Pending')
                    ->where('team_id', $user->team_id)
                    ->latest()
                    ->take(50)
                    ->get()
                    ->map(function (Conversion $conv) {
                        return [
                            'title' => 'Konversi menunggu verifikasi',
                            'message' => ucfirst($conv->type).' sebesar '.$conv->amount,
                            'status' => 'Pending',
                            'created_at' => $conv->created_at,
                        ];
                    })
            );
        }

        $collection = $collection
            ->filter(fn ($item) => ! empty($item['created_at']))
            ->sortByDesc('created_at')
            ->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $notifications = new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notifikasi ditandai sudah dibaca.');
    }
}
