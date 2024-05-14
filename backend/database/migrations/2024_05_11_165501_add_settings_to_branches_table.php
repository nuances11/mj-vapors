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
        Schema::table('branches', function (Blueprint $table) {
            $table->time('opening')->after('status')->default('08:00:00');
            $table->time('closing')->after('status')->default('21:00:00');
            $table->decimal('commission_threshold', 10, 2)->default(0);
            $table->decimal('commission_rate', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('time_in');
            $table->dropColumn('time_out');
            $table->dropColumn('commission_threshold');
            $table->dropColumn('commission_rate');
        });
    }
};
