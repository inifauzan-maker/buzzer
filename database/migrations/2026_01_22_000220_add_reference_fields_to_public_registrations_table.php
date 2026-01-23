<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->string('full_name', 255)->nullable()->after('id');
            $table->unsignedInteger('school_id')->nullable()->after('full_name');
            $table->string('school_name', 255)->nullable()->after('school_id');
            $table->string('class_level', 10)->nullable()->after('school_name');
            $table->string('study_location', 30)->nullable()->after('class_level');
            $table->string('phone_number', 20)->nullable()->after('study_location');
            $table->string('province', 120)->nullable()->after('phone_number');
            $table->string('city', 120)->nullable()->after('province');
            $table->string('district', 120)->nullable()->after('city');
            $table->string('subdistrict', 120)->nullable()->after('district');
            $table->string('postal_code', 10)->nullable()->after('subdistrict');
            $table->string('address_detail', 255)->nullable()->after('postal_code');
            $table->unsignedBigInteger('program_id')->nullable()->after('address_detail');

            $table->foreign('program_id')
                ->references('id')
                ->on('produk_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_registrations', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn([
                'full_name',
                'school_id',
                'school_name',
                'class_level',
                'study_location',
                'phone_number',
                'province',
                'city',
                'district',
                'subdistrict',
                'postal_code',
                'address_detail',
                'program_id',
            ]);
        });
    }
};
