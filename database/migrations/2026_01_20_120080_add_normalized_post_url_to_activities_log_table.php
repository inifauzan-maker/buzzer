<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities_log', function (Blueprint $table) {
            $table->string('normalized_post_url', 255)->nullable()->after('platform_post_id');
            $table->index(['team_id', 'platform', 'normalized_post_url'], 'activities_normalized_url_idx');
        });
    }

    public function down(): void
    {
        Schema::table('activities_log', function (Blueprint $table) {
            $table->dropIndex('activities_normalized_url_idx');
            $table->dropColumn('normalized_post_url');
        });
    }
};
