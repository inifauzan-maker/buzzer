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
            'share' => 5.0,
            'save' => 3.0,
            'comment' => 2.0,
            'like' => 1.0,
            'reach' => 0.001,
            'consistency_bonus' => 100.0,
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

        $points = 0.0;
        $points += $activity->shares * ($settings['share'] ?? 0);
        $points += $activity->saves * ($settings['save'] ?? 0);
        $points += $activity->comments * ($settings['comment'] ?? 0);
        $points += $activity->likes * ($settings['like'] ?? 0);
        $points += $activity->reach * ($settings['reach'] ?? 0);

        $multiplier = self::gradeMultiplier($activity->admin_grade);

        return round($points * $multiplier, 4);
    }

    public static function conversion(Conversion $conversion, ?array $settings = null): float
    {
        $settings ??= self::settings();

        $type = strtolower($conversion->type);
        $metric = $type === 'closing' ? 'closing' : 'lead';
        $base = $settings[$metric] ?? 0;

        return round($conversion->amount * $base, 4);
    }

    public static function gradeMultiplier(?string $grade): float
    {
        return match (strtoupper($grade ?? 'B')) {
            'A' => 1.2,
            'C' => 0.8,
            default => 1.0,
        };
    }
}
