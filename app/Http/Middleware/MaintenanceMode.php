<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = AppSetting::getValue('maintenance_enabled', '0');
        $isEnabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

        if (! $isEnabled) {
            return $next($request);
        }

        if ($request->is('login') || $request->is('logout') || $request->is('up')) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->role === 'superadmin') {
            return $next($request);
        }

        $message = AppSetting::getValue('maintenance_message', 'Sistem sedang dinonaktifkan sementara. Silakan coba lagi nanti.');

        return response()
            ->view('maintenance', ['message' => $message], 503);
    }
}
