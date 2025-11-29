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
        Schema::create('company_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained("products")->onDelete('cascade');
          // Offer Type
            $table->enum('offer_type', ['DISCOUNT', 'BUY_X_GET_Y'])->default('DISCOUNT');

            // Discount Fields
            $table->decimal('discount', 8, 2)->nullable();

            // Buy X Get Y Fields
            $table->unsignedInteger('get_free_quantity')->nullable();
            $table->unsignedInteger('max_redemption_per_invoice')->nullable();

            // Shared
            $table->unsignedInteger('min_quantity')->nullable(); // هذا هو Buy X
            $table->unsignedInteger('total_quantity')->default(1);

            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_offers');
    }
};
