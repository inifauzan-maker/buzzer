<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_name');
            $table->string('school_name')->nullable();
            $table->string('phone_number', 32)->nullable();
            $table->string('channel')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('prospect');
            $table->dateTime('follow_up_at')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('last_contact_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
