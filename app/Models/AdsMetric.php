<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdsMetric extends Model
{
    protected $fillable = [
        'ads_campaign_id',
        'report_date',
        'pic_id',
        'cost',
        'product',
        'content_url',
        'impressions',
        'reach',
        'clicks_wa',
        'leads_count',
        'closing_count',
        'views_3s',
        'views_50s',
        'reactions',
        'link_clicks',
        'saves',
        'shares',
        'profile_visits',
        'follows',
        'gender_male',
        'gender_female',
        'age_18_24',
        'age_25_34',
        'age_35_44',
        'age_45_54',
        'age_55_64',
        'age_65_plus',
        'top_locations',
    ];

    protected $casts = [
        'report_date' => 'date',
        'top_locations' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdsCampaign::class, 'ads_campaign_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
