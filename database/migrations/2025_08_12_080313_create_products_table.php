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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('scientific_name')->nullable();

            $table->string('bar_code')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('gtin')->nullable();
            $table->integer('position')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('show_home')->default(1);
            $table->text("active_ingredients",1000)->nullable();
            $table->text('description',1000)->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('dosage_form')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('rating', 2, 1)->default(0);
            $table->decimal('price', 5, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->text('search_index')->nullable();
         
            $table->fullText(['search_index']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
