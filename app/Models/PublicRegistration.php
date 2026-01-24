<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program',
        'email',
        'phone',
        'company',
        'message',
        'full_name',
        'birth_place',
        'birth_date',
        'school_id',
        'school_name',
        'class_level',
        'major',
        'shirt_size',
        'shirt_size_other',
        'social_media',
        'study_location',
        'phone_number',
        'province',
        'city',
        'district',
        'subdistrict',
        'postal_code',
        'address_detail',
        'program_id',
        'study_day',
        'study_time',
        'payment_system',
        'ip_address',
        'user_agent',
        'parent_name',
        'parent_phone',
        'parent_job',
        'referral_sources',
    ];
}
