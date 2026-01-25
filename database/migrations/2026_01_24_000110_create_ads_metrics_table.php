<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ads_campaign_id');
            $table->date('report_date')->nullable();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('product', 160)->nullable();
            $table->string('content_url')->nullable();
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('clicks_wa')->default(0);
            $table->unsignedInteger('leads_count')->default(0);
            $table->unsignedInteger('closing_count')->default(0);
            $table->unsignedInteger('views_3s')->default(0);
            $table->unsignedInteger('views_50s')->default(0);
            $table->unsignedInteger('reactions')->default(0);
            $table->unsignedInteger('link_clicks')->default(0);
            $table->unsignedInteger('saves')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('profile_visits')->default(0);
            $table->unsignedInteger('follows')->default(0);
            $table->decimal('gender_male', 5, 2)->nullable();
            $table->decimal('gender_female', 5, 2)->nullable();
            $table->decimal('age_18_24', 5, 2)->nullable();
            $table->decimal('age_25_34', 5, 2)->nullable();
            $table->decimal('age_35_44', 5, 2)->nullable();
            $table->decimal('age_45_54', 5, 2)->nullable();
            $table->decimal('age_55_64', 5, 2)->nullable();
            $table->decimal('age_65_plus', 5, 2)->nullable();
            $table->json('top_locations')->nullable();
            $table->timestamps();

            $table->foreign('ads_campaign_id')->references('id')->on('ads_campaigns')->cascadeOnDelete();
            $table->foreign('pic_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['ads_campaign_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads_metrics');
    }
};
