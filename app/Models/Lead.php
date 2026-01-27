<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'created_by',
        'assigned_to',
        'student_name',
        'school_name',
        'phone_number',
        'channel',
        'source',
        'status',
        'follow_up_at',
        'notes',
        'last_contact_at',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
        'last_contact_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }
}
