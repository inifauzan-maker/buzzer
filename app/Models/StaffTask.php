<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTask extends Model
{
    protected $fillable = [
        'leader_id',
        'staff_id',
        'team_id',
        'title',
        'notes',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
