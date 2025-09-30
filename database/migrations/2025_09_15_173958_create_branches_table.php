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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique(); 
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');

            $table->string('address');
            $table->boolean('active')->default(true)->index();

            $table->softDeletes();
            $table->timestamps();
            $table->index(['pharmacy_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
