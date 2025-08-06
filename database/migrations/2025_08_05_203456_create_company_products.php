<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('form'); // الشكل (شريط، زجاجة...)
            $table->string('concentration')->nullable(); 
            $table->integer('quantity')->default(0); 
            $table->decimal('price', 10, 2); // السعر
            $table->date('expiry_date')->nullable(); 
            $table->string('barcode')->nullable(); // الباركود
            $table->text('description')->nullable(); // وصف المنتج
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null'); // تصنيف المنتج
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // العلامة التجارية
            $table->string('country_of_origin')->nullable(); // بلد المنشأ
            $table->string('packaging')->nullable(); // التغليف
            $table->date('manufacture_date')->nullable(); // تاريخ التصنيع
            $table->date('registration_date')->nullable(); // تاريخ التسجيل
            $table->string('registration_number')->nullable(); // رقم التسجيل
            $table->string('active_ingredients')->nullable(); // المكونات النشطة
            $table->text('usage_instructions')->nullable(); // تعليمات الاستخدام
            $table->string('warnings')->nullable(); // التحذيرات
            $table->string('unit')->default('pcs'); // الوحدة (قطعة، علبة...)
            $table->string('unit_type')->default('count');
            $table->enum('availability_status', ['available', 'out_of_stock', 'pending'])->default('available'); // حالة التوفر
            $table->string('manufacturer')->nullable(); // الشركة المصنعة
            $table->string('storage_conditions')->nullable(); // شروط التخزين
            $table->string('dosage')->nullable(); // الجرعة
            $table->text('side_effects')->nullable(); // الآثار الجانبية
            $table->boolean('approval_status')->default(true); // حالة الموافقة
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade'); // علاقة بالشركة
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_products');
    }
};
