<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Row;

class WarehouseProductBatchImport implements OnEachRow, WithChunkReading, SkipsEmptyRows, ShouldQueue
{
    protected $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    /**
     * كل صف من الملف يتعامل هنا
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['bar_code']) || empty($data['batch_number']) || empty($data['stock'])) {
            return; // تجاهل الصفوف الفارغة أو الناقصة
        }

        $data = [
            'bar_code'     => trim($data['bar_code']),
            'batch_number' => trim($data['batch_number']),
            'stock'        => (int) $data['stock'],
            'expiry_date'  => $data['expiry_date'] ?? null,
        ];

        // التحقق من صحة البيانات
        $validator = Validator::make($data, [
            'bar_code'     => 'required|string|exists:products,bar_code',
            'batch_number' => 'required|string',
            'stock'        => 'required|integer|min:1',
            'expiry_date'  => 'nullable|date',
        ]);

        if ($validator->fails()) {
            Log::error("Row {$row->getIndex()} validation error: " . json_encode($validator->errors()->all()));
            return;
        }

        // جلب المنتج المطابق
        $product = Product::where('bar_code', $data['bar_code'])->first();
        if (!$product) {
            Log::error("Row {$row->getIndex()} error: المنتج ذو الكود {$data['bar_code']} غير موجود.");
            return;
        }

        // تجهيز البيانات للـ DB
        $insertData = [
            'warehouse_id' => $this->warehouse->id,
            'product_id'   => $product->id,
            'batch_number' => $data['batch_number'],
            'stock'        => $data['stock'],
            'expiry_date'  => $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
        ];

        // استخدام Transaction + دمج المخزون
        DB::transaction(function () use ($insertData) {
            $key = $insertData['product_id'] . '-' . $insertData['batch_number'] . '-' . ($insertData['expiry_date'] ?? 'null');

            // جلب الـ batch الموجود إذا كان موجود مسبقًا
            $existingBatch = WarehouseProductBatch::where('warehouse_id', $insertData['warehouse_id'])
                ->where('product_id', $insertData['product_id'])
                ->where('batch_number', $insertData['batch_number'])
                ->where(function ($q) use ($insertData) {
                    if ($insertData['expiry_date']) {
                        $q->where('expiry_date', $insertData['expiry_date']);
                    } else {
                        $q->whereNull('expiry_date');
                    }
                })
                ->first();

            if ($existingBatch) {
                // دمج المخزون
                $existingBatch->increment('stock', $insertData['stock']);
            } else {
                // ربط المنتج بالمخزن إذا لم يكن موجود
                $warehouse = $this->warehouse;
                if (!$warehouse->products()->where('product_id', $insertData['product_id'])->exists()) {
                    $warehouse->products()->attach($insertData['product_id'], ['reserved_stock' => 0]);
                }

                // إنشاء Batch جديد
                WarehouseProductBatch::create($insertData);
            }
        });
    }

    /**
     * قراءة الملف على أجزاء لتقليل استهلاك الذاكرة
     */
    public function chunkSize(): int
    {
        return 500; // يمكن تغييره حسب حجم الملف
    }
}
