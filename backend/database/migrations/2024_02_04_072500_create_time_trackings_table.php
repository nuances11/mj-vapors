<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->time('work_time');
            $table->date('work_date');
            $table->dateTime('clock_in');
            $table->dateTime('clock_out');
            $table->dateTime('break_in');
            $table->dateTime('break_out');
            $table->integer('break_time_in_seconds');
            $table->integer('total_hours_in_seconds');
            $table->enum('last_action', ['clock_in', 'break_in', 'break_out', 'clock_out']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_trackings');
    }
};
