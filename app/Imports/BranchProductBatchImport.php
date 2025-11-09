<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProductBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BranchProductBatchImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $branch;
    protected $errors = [];

    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }

    public function collection(Collection $rows)
    {
        $validRows = [];

        $barcodes = $rows->pluck('bar_code')->filter()->unique()->toArray();

        $products = Product::whereIn('bar_code', $barcodes)->pluck('id', 'bar_code');

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
                'branch_id' => $this->branch->id,
                'product_id'   => $products[$data['bar_code']],
                'batch_number' => trim($data['batch_number']),
                'stock'        => $data['stock'],
                'expiry_date'  => $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
            ];
        }

        if (!empty($this->errors)) {
            throw new \Exception(json_encode($this->errors, JSON_UNESCAPED_UNICODE));
        }

        /**
         *  الخطوة 1: دمج الصفوف المتكررة داخل نفس الملف
         * نفس المنتج + نفس الباتش + نفس تاريخ الانتهاء = جمع المخزون
         */
        $mergedRows = [];
        foreach ($validRows as $data) {
            $key = $data['product_id'] . '-' . $data['batch_number'] . '-' . ($data['expiry_date'] ?? 'null');

            if (isset($mergedRows[$key])) {
                $mergedRows[$key]['stock'] += $data['stock'];
            } else {
                $mergedRows[$key] = $data;
            }
        }

        $validRows = array_values($mergedRows);

        /**
         * الخطوة 2: إدخال أو تحديث البيانات في الداتابيز
         */
        DB::transaction(function () use ($validRows) {
            $branch = $this->branch;

            // جلب كل الـ batches الموجودة بالفعل في المخزن
            $existingBatches = BranchProductBatch::where('branch_id', $branch->id)
                ->get(['product_id', 'batch_number', 'expiry_date', 'id', 'stock'])
                ->mapWithKeys(function ($item) {
                    $batchNumber = trim($item->batch_number);
                    $expiryDate = $item->expiry_date ? date('Y-m-d', strtotime($item->expiry_date)) : 'null';
                    return [
                        $item->product_id . '-' . $batchNumber . '-' . $expiryDate => $item
                    ];
                });

            foreach ($validRows as $data) {
                $batchNumber = trim($data['batch_number']);
                $expiryDate = $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : 'null';
                $key = $data['product_id'] . '-' . $batchNumber . '-' . $expiryDate;

                if (isset($existingBatches[$key])) {
                    
                    $existingBatches[$key]->increment('stock', $data['stock']);
                } else {
                   
                    if (!$branch->products()->where('product_id', $data['product_id'])->exists()) {
                        $branch->products()->attach($data['product_id'], ['reserved_stock' => 0]);
                    }

                   
                    BranchProductBatch::create($data);
                }
            }
        });
    }
}
