<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->string('birth_place', 120)->nullable()->after('full_name');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('major', 50)->nullable()->after('class_level');
            $table->string('shirt_size', 20)->nullable()->after('major');
            $table->string('shirt_size_other', 50)->nullable()->after('shirt_size');
            $table->string('social_media', 50)->nullable()->after('shirt_size_other');
            $table->string('study_day', 20)->nullable()->after('program_id');
            $table->string('study_time', 50)->nullable()->after('study_day');
            $table->string('payment_system', 20)->nullable()->after('study_time');
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'birth_date',
                'major',
                'shirt_size',
                'shirt_size_other',
                'social_media',
                'study_day',
                'study_time',
                'payment_system',
            ]);
        });
    }
};
