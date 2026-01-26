<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'validation_status',
        'validated_at',
        'validated_by',
        'invoice_number',
        'invoice_total',
        'remaining_balance',
        'payment_status',
        'payment_amount',
        'payment_proof_path',
        'payment_submitted_at',
        'payment_verified_at',
        'payment_verified_by',
        'payment_invoice_number',
        'payment_invoice_issued_at',
        'academic_forwarded_at',
        'academic_forwarded_by',
        'invoice_sent_at',
        'invoice_sent_to',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'validated_at' => 'datetime',
        'invoice_sent_at' => 'datetime',
        'invoice_total' => 'integer',
        'remaining_balance' => 'integer',
        'payment_amount' => 'integer',
        'payment_submitted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_invoice_issued_at' => 'datetime',
        'academic_forwarded_at' => 'datetime',
    ];

    public function programItem(): BelongsTo
    {
        return $this->belongsTo(ProdukItem::class, 'program_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function academicForwardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_forwarded_by');
    }
}
