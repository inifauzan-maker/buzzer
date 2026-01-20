<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities_log', function (Blueprint $table) {
            $table->string('platform_post_id', 120)->nullable()->after('post_url');
            $table->index(['team_id', 'platform', 'platform_post_id'], 'activities_platform_post_idx');
        });
    }

    public function down(): void
    {
        Schema::table('activities_log', function (Blueprint $table) {
            $table->dropIndex('activities_platform_post_idx');
            $table->dropColumn('platform_post_id');
        });
    }
};
