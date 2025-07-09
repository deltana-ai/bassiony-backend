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
        Schema::create('medicine_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
                $table->unique(['medicine_id', 'schedule_id', 'scheduled_time'], 'unique_schedule_per_time');

            $table->foreignId('schedule_id')->constrained('medicine_schedules')->onDelete('cascade');
            $table->dateTime('scheduled_time');
            $table->dateTime('actual_time')->nullable();
            $table->boolean('taken')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_intakes');
    }
};
