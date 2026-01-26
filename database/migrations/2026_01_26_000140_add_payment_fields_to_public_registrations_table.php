<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->after('remaining_balance');
            $table->unsignedBigInteger('payment_amount')->nullable()->after('payment_status');
            $table->string('payment_proof_path', 180)->nullable()->after('payment_amount');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_proof_path');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_submitted_at');
            $table->unsignedBigInteger('payment_verified_by')->nullable()->after('payment_verified_at');
            $table->string('payment_invoice_number', 30)->nullable()->after('payment_verified_by');
            $table->timestamp('payment_invoice_issued_at')->nullable()->after('payment_invoice_number');

            $table->foreign('payment_verified_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropForeign(['payment_verified_by']);
            $table->dropColumn([
                'payment_status',
                'payment_amount',
                'payment_proof_path',
                'payment_submitted_at',
                'payment_verified_at',
                'payment_verified_by',
                'payment_invoice_number',
                'payment_invoice_issued_at',
            ]);
        });
    }
};
