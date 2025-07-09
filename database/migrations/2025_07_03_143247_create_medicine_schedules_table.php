<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
      Schema::create('medicine_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
    $table->json('times');
    $table->json('days');
    $table->timestamps();
    
    // إضافة فهارس لتحسين الأداء
    $table->index(['medicine_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_schedules');
    }
};
