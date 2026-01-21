<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('target_closing')->default(0);
            $table->unsignedInteger('target_leads')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_targets');
    }
};
