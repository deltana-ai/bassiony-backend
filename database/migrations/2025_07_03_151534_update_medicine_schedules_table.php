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
     Schema::table('medicine_schedules', function (Blueprint $table) {
        $table->json('times')->nullable()->change();
        $table->json('days')->nullable()->change();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_schedules', function (Blueprint $table) {
        $table->json('times')->change();
        $table->json('days')->change();
    });
    }
};
