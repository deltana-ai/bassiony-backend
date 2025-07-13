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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index()->name('medications_user_id_foreign');
            $table->string('name');
            $table->text('note')->nullable();
            $table->integer('dose_amount'); // مثال: 1 أو 2
            $table->integer('times_per_day'); // عدد مرات الجرعة في اليوم
            $table->date('start_date')->index(); // بداية الشهر
            $table->date('end_date')->index();   // نهاية الشهر
            $table->boolean('is_expired')->default(false)->index(); // جرعة منتهية؟
            $table->boolean('user_approved_deletion')->nullable();  // true = احذف, false = مدد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
