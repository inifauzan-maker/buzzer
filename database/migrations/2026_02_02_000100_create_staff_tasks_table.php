<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leader_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_tasks');
    }
};
