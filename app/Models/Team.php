<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\TeamTarget;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_name',
        'reminder_phone',
        'last_reminded_at',
    ];

    protected $casts = [
        'last_reminded_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    public function leader(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'leader');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(TeamTarget::class);
    }
}
