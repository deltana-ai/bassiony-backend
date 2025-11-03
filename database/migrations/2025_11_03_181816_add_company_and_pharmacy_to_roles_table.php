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
        Schema::table('roles', function (Blueprint $table) {
             if (!Schema::hasColumn('roles', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('guard_name')->constrained('companies')->nullOnDelete();
            }

            if (!Schema::hasColumn('roles', 'pharmacy_id')) {
                $table->foreignId('pharmacy_id')->nullable()->after('company_id')->constrained('pharmacies')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
             if (Schema::hasColumn('roles', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }

            if (Schema::hasColumn('roles', 'pharmacy_id')) {
                $table->dropConstrainedForeignId('pharmacy_id');
            }
        });
    }
};
