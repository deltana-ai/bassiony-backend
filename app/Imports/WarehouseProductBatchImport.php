<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class WarehouseProductBatchImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithChunkReading, SkipsEmptyRows
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
        // استخراج الأكواد الفريدة في هذا الـ Chunk
        $barcodes = $rows->pluck('bar_code')->filter()->unique()->toArray();
        $products = Product::whereIn('bar_code', $barcodes)->pluck('id', 'bar_code');

        $mergedRows = [];

        foreach ($rows as $index => $row) {
            if (empty($row['bar_code']) || empty($row['batch_number'])) continue;

            $data = [
                'bar_code'     => trim($row['bar_code']),
                'batch_number' => trim($row['batch_number']),
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

            // دمج الصفوف المتكررة داخل هذا الـ Chunk
            $key = $products[$data['bar_code']] . '-' . $data['batch_number'] . '-' . ($data['expiry_date'] ?? 'null');

            if (isset($mergedRows[$key])) {
                $mergedRows[$key]['stock'] += $data['stock'];
            } else {
                $mergedRows[$key] = [
                    'warehouse_id' => $this->warehouse->id,
                    'product_id'   => $products[$data['bar_code']],
                    'batch_number' => $data['batch_number'],
                    'stock'        => $data['stock'],
                    'expiry_date'  => $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
                ];
            }
        }

        // إدخال أو تحديث البيانات لكل Chunk
        DB::transaction(function () use ($mergedRows) {
            $warehouse = $this->warehouse;

            // جلب الـ batches الموجودة بالفعل في المخزن
            $existingBatches = WarehouseProductBatch::where('warehouse_id', $warehouse->id)
                ->get(['product_id', 'batch_number', 'expiry_date', 'id', 'stock'])
                ->mapWithKeys(function ($item) {
                    $batchNumber = trim($item->batch_number);
                    $expiryDate = $item->expiry_date ? date('Y-m-d', strtotime($item->expiry_date)) : 'null';
                    return [$item->product_id . '-' . $batchNumber . '-' . $expiryDate => $item];
                });

            $toInsert = [];

            foreach ($mergedRows as $data) {
                $key = $data['product_id'] . '-' . $data['batch_number'] . '-' . ($data['expiry_date'] ?? 'null');

                if (isset($existingBatches[$key])) {
                    $existingBatches[$key]->increment('stock', $data['stock']);
                } else {
                    if (!$warehouse->products()->where('product_id', $data['product_id'])->exists()) {
                        $warehouse->products()->attach($data['product_id'], ['reserved_stock' => 0]);
                    }
                    $toInsert[] = $data;
                }
            }

            if (!empty($toInsert)) {
                WarehouseProductBatch::insert($toInsert);
            }
        });
    }

    public function chunkSize(): int
    {
        return 500; // حجم الـ Chunk
    }
}
