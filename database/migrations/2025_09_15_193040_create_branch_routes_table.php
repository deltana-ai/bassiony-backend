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
        Schema::create('branch_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name', 100);
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->json('locations')->nullable(); //array of locations ids
            $table->decimal('estimated_distance', 8, 2)->nullable(); // in km
            $table->integer('estimated_duration')->nullable(); // in minutes
            $table->decimal('base_shipping_cost', 8, 2)->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_routes');
    }
};
