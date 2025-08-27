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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // 👈 هنا التغيير
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->nullable(); // سعر الوحدة وقت الطلب
            $table->decimal('total', 10, 2)->nullable(); // سعر الوحدة * الكمية
            $table->string('return_number')->unique()->nullable();
            $table->string('reason')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
