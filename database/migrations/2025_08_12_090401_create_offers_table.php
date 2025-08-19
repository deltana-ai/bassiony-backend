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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
