<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->timestamp('academic_forwarded_at')->nullable()->after('payment_invoice_issued_at');
            $table->unsignedBigInteger('academic_forwarded_by')->nullable()->after('academic_forwarded_at');

            $table->foreign('academic_forwarded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropForeign(['academic_forwarded_by']);
            $table->dropColumn(['academic_forwarded_at', 'academic_forwarded_by']);
        });
    }
};
