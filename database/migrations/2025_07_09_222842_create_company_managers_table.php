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
        Schema::create('company_managers', function (Blueprint $table) {
          $table->id();
          $table->string('name');
          $table->string('email')->unique();
          $table->text('address')->nullable();
          $table->softDeletes();

          $table->timestamp('email_verified_at')->nullable();
          $table->string('firebase_uid')->nullable()->unique();
          $table->string('phone')->nullable()->unique();
          $table->boolean('is_verified')->default(true);
          $table->string('password');
          $table->timestamp('last_login_at')->nullable();

          $table->rememberToken();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_managers');
    }
};
