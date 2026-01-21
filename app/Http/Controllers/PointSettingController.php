<?php

namespace App\Http\Controllers;

use App\Models\PointSetting;
use App\Services\PointCalculator;
use Illuminate\Http\Request;

class PointSettingController extends Controller
{
    public function index()
    {
        $settings = PointSetting::query()->get()->keyBy('metric_name');
        $defaults = PointCalculator::defaultSettings();
        $labels = $this->labels();

        $rows = [];
        foreach ($defaults as $metric => $value) {
            $rows[] = [
                'metric' => $metric,
                'label' => $labels[$metric] ?? $metric,
                'value' => $settings[$metric]->point_value ?? $value,
            ];
        }

        return view('settings-points', [
            'rows' => $rows,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'point_settings' => 'required|array',
            'point_settings.*' => 'required|numeric|min:0',
        ]);

        foreach ($data['point_settings'] as $metric => $value) {
            PointSetting::updateOrCreate(
                ['metric_name' => $metric],
                ['point_value' => $value]
            );
        }

        return redirect()
            ->route('settings.points')
            ->with('status', 'Bobot poin berhasil diperbarui.');
    }

    private function labels(): array
    {
        return [
            'closing' => 'Closing / transaksi',
            'lead' => 'Lead / kontak',
            'er_good_min' => 'ER min Good (%)',
            'er_high_min' => 'ER min High (%)',
            'er_viral_min' => 'ER min Viral (%)',
            'er_good_points' => 'Poin Good (>=1% - <3%)',
            'er_high_points' => 'Poin High (>=3% - <6%)',
            'er_viral_points' => 'Poin Viral (>=6%)',
        ];
    }
}
