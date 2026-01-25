<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdsCampaign extends Model
{
    protected $fillable = [
        'name',
        'platform',
        'objective',
        'brief',
        'target_audience',
        'budget_plan',
        'start_date',
        'end_date',
        'status',
        'kpi_leads',
        'kpi_closing',
        'kpi_reach',
        'pic_id',
        'created_by',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(AdsMetric::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
