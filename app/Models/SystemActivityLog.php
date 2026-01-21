<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'role',
        'activity',
        'ip_address',
        'user_agent',
    ];
}
