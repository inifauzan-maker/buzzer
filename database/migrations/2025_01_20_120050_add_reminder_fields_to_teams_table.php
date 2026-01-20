<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('reminder_phone', 30)->nullable()->after('team_name');
            $table->timestamp('last_reminded_at')->nullable()->after('reminder_phone');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['reminder_phone', 'last_reminded_at']);
        });
    }
};
