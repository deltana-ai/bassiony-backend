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
        Schema::create('pharmacy_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'delivered','shipped', 'cancelled','completed'])->default('pending');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card'])->default('cash');
            $table->text('notes')->nullable();
 
            $table->timestamps();
            $table->index(['company_id']);
            $table->index(['pharmacy_id']);
            $table->index(['status']);
           

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_orders');
    }
};
