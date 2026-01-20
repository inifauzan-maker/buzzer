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

        foreach ($settings as $metric => $setting) {
            if (!array_key_exists($metric, $defaults)) {
                $rows[] = [
                    'metric' => $metric,
                    'label' => $labels[$metric] ?? $metric,
                    'value' => $setting->point_value,
                ];
            }
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
            'share' => 'Share',
            'save' => 'Save',
            'comment' => 'Comment',
            'like' => 'Like',
            'reach' => 'Reach (poin per 1 reach, isi 0.001 untuk 1/1000)',
            'consistency_bonus' => 'Bonus konsistensi mingguan',
        ];
    }
}
