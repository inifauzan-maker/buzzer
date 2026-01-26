<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->string('validation_status', 20)->default('pending')->after('payment_system');
            $table->timestamp('validated_at')->nullable()->after('validation_status');
            $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
            $table->string('invoice_number', 30)->nullable()->after('validated_by');
            $table->unsignedBigInteger('invoice_total')->nullable()->after('invoice_number');
            $table->timestamp('invoice_sent_at')->nullable()->after('invoice_total');
            $table->string('invoice_sent_to', 20)->nullable()->after('invoice_sent_at');

            $table->foreign('validated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'validation_status',
                'validated_at',
                'validated_by',
                'invoice_number',
                'invoice_total',
                'invoice_sent_at',
                'invoice_sent_to',
            ]);
        });
    }
};
