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
        Schema::create('response_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_offer_id')->constrained("company_offers")->onDelete('cascade');
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('total_price', 10, 2);
            $table->decimal('item_price',10,2)->default(1);

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('total_free_quantity')->default(0);
            $table->unsignedInteger('all_quantity')->default(0);

            $table->enum('status',["pending","approved","rejected","delivered","canceled","returned","completed"])->default("pending");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_offer');
    }
};
