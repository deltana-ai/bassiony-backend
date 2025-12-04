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
        Schema::create('pharmacy_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_offer_id')->nullable()->constrained("company_offers")->onDelete('set null');
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);

            $table->decimal('total_price', 10, 2);

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('total_free_quantity')->default(0);
            $table->unsignedInteger('all_quantity');
            $table->string('return_number')->unique()->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_order_items');
    }
};
