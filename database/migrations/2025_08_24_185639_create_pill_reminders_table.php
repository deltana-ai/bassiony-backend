<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pill_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الدواء
            $table->string('dosage')->nullable(); // الجرعة
            $table->text('notes')->nullable(); // ملاحظات
            $table->time('time'); // وقت التذكير
            $table->boolean('repeat')->default(false); // هل متكرر؟
            $table->json('days')->nullable(); // الأيام اللي بيكرر فيها
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // خاص بالمستخدم
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pill_reminders');
    }
};
