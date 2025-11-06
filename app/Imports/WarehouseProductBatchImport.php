<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WarehouseProductBatchImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $warehouse;
    protected $errors = [];

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function collection(Collection $rows)
    {
        $validRows = [];
		
        $barcodes = $rows->pluck('bar_code')->filter()->unique()->toArray();

        // جلب كل المنتجات المطابقة مرة واحدة
        $products = Product::whereIn('bar_code', $barcodes)
            ->pluck('id', 'bar_code'); 
			
        // المرحلة الأولى: التحقق من صحة البيانات
        foreach ($rows as $index => $row) {
            if (empty($row['bar_code']) || empty($row['batch_number'])) {
                continue;
            }

            $data = [
                'bar_code'     => trim($row['bar_code'] ?? ''),
                'batch_number' => trim($row['batch_number'] ?? ''),
                'stock'        => (int) ($row['stock'] ?? 0),
                'expiry_date'  => $row['expiry_date'] ?? null,
            ];

            $validator = Validator::make($data, [
                'bar_code'     => 'required|string|exists:products,bar_code',
                'batch_number' => 'required|string',
                'stock'        => 'required|integer|min:1',
                'expiry_date'  => 'nullable|date',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            if (!isset($products[$data['bar_code']])) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => ["المنتج ذو الكود {$data['bar_code']} غير موجود."],
                ];
                continue;
            }

            $validRows[] = [
                'warehouse_id' => $this->warehouse->id,
                'product_id'   => $product->id,
                'batch_number' => $data['batch_number'],
                'stock'        => $data['stock'],
                'expiry_date'  => $data['expiry_date'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        //  لو في أخطاء وقفي الاستيراد
        if (!empty($this->errors)) {
            throw new \Exception(json_encode($this->errors, JSON_UNESCAPED_UNICODE));
        }

        //  تحسين الأداء عن طريق تحميل البيانات الحالية مرة واحدة
        DB::transaction(function () use ($validRows) {
            $warehouse = $this->warehouse;

            // تحميل كل الـ batches الموجودة مسبقًا في هذا المخزن
            $existingBatches = WarehouseProductBatch::where('warehouse_id', $warehouse->id)
                ->get(['product_id', 'batch_number', 'expiry_date', 'id', 'stock'])
                ->keyBy(fn($item) => $item->product_id . '-' . $item->batch_number . '-' . $item->expiry_date);

            // تحميل كل المنتجات الموجودة بالفعل في pivot warehouse_product
            $existingProducts = $warehouse->products()->pluck('product_id')->toArray();

            //  المعالجة bulk
            foreach ($validRows as $data) {
                $key = $data['product_id'] . '-' . $data['batch_number'] . '-' . $data['expiry_date'];

                if (isset($existingBatches[$key])) {
                    //  تحديث الكمية فقط (بدون تحميل الموديل)
                    DB::table('warehouse_product_batches')
                        ->where('id', $existingBatches[$key]->id)
                        ->update([
                            'stock' => DB::raw('stock + ' . $data['stock']),
                            'updated_at' => now(),
                        ]);
                } else {
                    //  تأكيد وجود علاقة المنتج بالمخزن (pivot)
                    if (!in_array($data['product_id'], $existingProducts)) {
                        $warehouse->products()->attach($data['product_id'], ['reserved_stock' => 0]);
                        $existingProducts[] = $data['product_id']; // علشان ما تتكررش الإضافة
                    }

                    //  إنشاء batch جديد
                    WarehouseProductBatch::create($data);

                    // أضيفه في الكاش المحلي علشان لو تكرر في نفس الملف
                    $existingBatches[$key] = (object) $data;
                }
            }
        });
    }
}
