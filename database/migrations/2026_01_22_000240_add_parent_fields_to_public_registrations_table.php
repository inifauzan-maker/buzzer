<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->string('parent_name', 255)->nullable()->after('program_id');
            $table->string('parent_phone', 20)->nullable()->after('parent_name');
            $table->string('parent_job', 120)->nullable()->after('parent_phone');
            $table->string('referral_sources', 255)->nullable()->after('parent_job');
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'parent_name',
                'parent_phone',
                'parent_job',
                'referral_sources',
            ]);
        });
    }
};
