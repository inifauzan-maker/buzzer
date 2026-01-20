<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'point_value',
    ];

    protected $casts = [
        'point_value' => 'float',
    ];
}
