<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('platform', 40);
            $table->string('objective', 40);
            $table->text('brief')->nullable();
            $table->text('target_audience')->nullable();
            $table->decimal('budget_plan', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('Draft');
            $table->unsignedInteger('kpi_leads')->default(0);
            $table->unsignedInteger('kpi_closing')->default(0);
            $table->unsignedInteger('kpi_reach')->default(0);
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('pic_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['platform', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads_campaigns');
    }
};
