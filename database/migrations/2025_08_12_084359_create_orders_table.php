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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // ✅ خليها nullable
            $table->foreignId('pharmacist_id')->nullable()->constrained('pharmacists')->nullOnDelete();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete(); // ✅ مضافة
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'delivered'])->default('pending');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'insurance'])->default('cash');
            $table->decimal('rating', 2, 1)->nullable();
            $table->text('review')->nullable();
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
