<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\PointSetting;

class PointCalculator
{
    public static function defaultSettings(): array
    {
        return [
            'closing' => 50.0,
            'lead' => 10.0,
            'er_good_min' => 1.0,
            'er_high_min' => 3.0,
            'er_viral_min' => 6.0,
            'er_good_points' => 10.0,
            'er_high_points' => 30.0,
            'er_viral_points' => 50.0,
        ];
    }

    public static function settings(): array
    {
        $stored = PointSetting::query()
            ->pluck('point_value', 'metric_name')
            ->map(fn ($value) => (float) $value)
            ->all();

        return array_merge(self::defaultSettings(), $stored);
    }

    public static function activity(ActivityLog $activity, ?array $settings = null): float
    {
        $settings ??= self::settings();

        $engagement = $activity->likes + $activity->comments + $activity->saves + $activity->shares;
        $reach = $activity->reach;

        if ($reach <= 0) {
            return 0.0;
        }

        $rate = ($engagement / $reach) * 100;
        $goodMin = (float) ($settings['er_good_min'] ?? 1);
        $highMin = (float) ($settings['er_high_min'] ?? 3);
        $viralMin = (float) ($settings['er_viral_min'] ?? 6);
        $goodPoints = (float) ($settings['er_good_points'] ?? 10);
        $highPoints = (float) ($settings['er_high_points'] ?? 30);
        $viralPoints = (float) ($settings['er_viral_points'] ?? 50);

        if ($rate >= $viralMin) {
            return $viralPoints;
        }

        if ($rate >= $highMin) {
            return $highPoints;
        }

        if ($rate >= $goodMin) {
            return $goodPoints;
        }

        return 0.0;
    }

    public static function conversion(Conversion $conversion, ?array $settings = null): float
    {
        $settings ??= self::settings();

        $type = strtolower($conversion->type);
        $metric = $type === 'closing' ? 'closing' : 'lead';
        $base = $settings[$metric] ?? 0;

        return round($conversion->amount * $base, 4);
    }

}
