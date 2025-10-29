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
            $table->decimal('total_price', 10, 2);
            $table->decimal('item_price',10,2)->default(1);

            $table->integer('quantity');
            $table->enum('status',["pending","approved","rejected","delivered","canceled"])->default("pending");
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
