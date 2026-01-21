<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\ChatMessage;
use App\Models\Conversion;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layout', function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            $notifCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            if ($user->role === 'superadmin') {
                $notifCount += ActivityLog::where('status', 'Reviewed')->count()
                    + Conversion::where('status', 'Reviewed')->count();
            } elseif ($user->role === 'leader') {
                $notifCount += ActivityLog::where('status', 'Pending')
                        ->where('team_id', $user->team_id)
                        ->count()
                    + Conversion::where('status', 'Pending')
                        ->where('team_id', $user->team_id)
                        ->count();
            }

            $chatUnreadCount = 0;
            if (in_array($user->role, ['leader', 'staff'], true)) {
                $chatUnreadCount = ChatMessage::query()
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', $user->id)
                    ->whereHas('thread', function ($query) use ($user) {
                        $query->where('user_one_id', $user->id)
                            ->orWhere('user_two_id', $user->id);
                    })
                    ->count();
            }

            $view->with('notifCount', $notifCount);
            $view->with('chatUnreadCount', $chatUnreadCount);
        });
    }
}
