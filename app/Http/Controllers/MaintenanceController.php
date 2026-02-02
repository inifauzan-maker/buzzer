<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\SystemActivityLogger;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $enabled = AppSetting::getValue('maintenance_enabled', '0');
        $message = AppSetting::getValue('maintenance_message', '');

        return view('maintenance-settings', [
            'maintenanceEnabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            'maintenanceMessage' => $message,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'message' => 'nullable|string|max:500',
        ]);

        $enabled = !empty($data['enabled']) ? '1' : '0';
        AppSetting::setValue('maintenance_enabled', $enabled);
        AppSetting::setValue('maintenance_message', $data['message'] ?? '');

        SystemActivityLogger::log($request->user(), 'Mengubah status maintenance aplikasi.');

        return redirect()
            ->route('settings.maintenance')
            ->with('status', 'Pengaturan maintenance diperbarui.');
    }
}
