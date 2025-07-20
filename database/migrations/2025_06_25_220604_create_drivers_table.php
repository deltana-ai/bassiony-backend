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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->unique('email');

            $table->string('firebase_uid')->nullable();
            $table->unique('firebase_uid');
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_admin')->default(false);

   
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
          $table->dropSoftDeletes();

        });
        Schema::dropIfExists('drivers');
    }
};
