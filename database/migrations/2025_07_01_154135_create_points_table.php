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
      Schema::create('points', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable(); // لو النقاط للمستخدم
    $table->unsignedBigInteger('pharmacist_id')->nullable(); // لو النقاط للصيدلي
    $table->unsignedBigInteger('company_id')->nullable(); // الشركة اللي منحت النقاط

    $table->enum('type', ['earned', 'spent', 'expired']); // نوع النقاط
    $table->integer('amount');
    $table->string('source_name'); // اسم الشركة أو الصيدلي اللي منح النقاط
    $table->timestamp('expires_at')->nullable(); // لو لها تاريخ صلاحية
    $table->timestamps();

    // علاقات
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('pharmacist_id')->references('id')->on('pharmacists')->onDelete('cascade');
    $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
    
    
    // فهارس لتحسين الأداء        $table->index(['user_id', 'type']);
        $table->index(['user_id', 'type', 'expires_at']);
        $table->index('created_at');
        $table->index('expires_at');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};
