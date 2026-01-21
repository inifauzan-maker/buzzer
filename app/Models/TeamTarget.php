<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTarget extends Model
{
    protected $fillable = [
        'team_id',
        'year',
        'month',
        'target_closing',
        'target_leads',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
