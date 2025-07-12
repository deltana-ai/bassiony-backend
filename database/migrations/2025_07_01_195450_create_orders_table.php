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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
            $table->enum('status',['pending','confirmed','returned','shipped','delivered','cancelled'])->default('pending');
            $table->decimal('subtotal',10,2)->nullable();
            $table->decimal('order_discount',10,2)->nullable();
            $table->decimal('total',10,2)->nullable();
            $table->decimal('paid_from_wallet',10,2)->nullable();
            $table->decimal('paid_by_card',10,2)->nullable();
            $table->boolean('is_paid')->nullable();

            $table->enum('payment_type',['wallet','cash','card','zain','deferred'])->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('paid_amount',10,2)->default(0);
            $table->decimal('remaining_amount',10,2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
