<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activities_log';

    protected $fillable = [
        'user_id',
        'team_id',
        'platform',
        'post_url',
        'platform_post_id',
        'normalized_post_url',
        'post_date',
        'likes',
        'comments',
        'shares',
        'saves',
        'reach',
        'evidence_screenshot',
        'status',
        'computed_points',
    ];

    protected $casts = [
        'post_date' => 'date',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
        'saves' => 'integer',
        'reach' => 'integer',
        'computed_points' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
