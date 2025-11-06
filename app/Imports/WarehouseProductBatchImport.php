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

        // المرحلة الأولى: التحقق من صحة البيانات (Validation فقط)
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

            $product = Product::where('bar_code', $data['bar_code'])->first();

            if (!$product) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => ["المنتج ذو الكود {$data['bar_code']} غير موجود."],
                ];
                continue;
            }

            //  data handeling to insert
            $validRows[] = [
                'warehouse_id' => $this->warehouse->id,
                'product_id'   => $product->id,
                'batch_number' => $data['batch_number'],
                'stock'        => $data['stock'],
                'expiry_date'  => $data['expiry_date'],
            ];
        }
        // if error exist stop importing
        if (!empty($this->errors)) {
            throw new \Exception(json_encode($this->errors, JSON_UNESCAPED_UNICODE));
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $data) {
                $batch = WarehouseProductBatch::where([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id'   => $data['product_id'],
                    'batch_number' => $data['batch_number'],
                    'expiry_date'  => $data['expiry_date'],
                ])->first();

                if ($batch) {
                    $batch->increment('stock', $data['stock']);
                }
              else{

                $warehouse = $this->warehouse;

                if (!$warehouse->products()->where('product_id', $data['product_id'])->exists()) {
                    $warehouse->products()->attach($data['product_id'], ['reserved_stock' => 0]);
                }

                WarehouseProductBatch::create($data);
              }
            }
        });
    }
}
