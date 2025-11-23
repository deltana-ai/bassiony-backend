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
        // Extract unique barcodes and GTINs from this chunk
        $barcodes = $rows->pluck('bar_code')->filter()->map(fn($v) => trim($v))->unique()->toArray();
        
        $gtins = $rows->pluck('gtin')->filter()->map(fn($v) => trim($v))->unique()->toArray();
       
        // Fetch products by barcode or GTIN
        $productsByBarcode = Product::whereIn('bar_code', $barcodes)->get()->keyBy('bar_code');
        $productsByGtin = Product::whereIn('gtin', $gtins)->get()->keyBy('gtin');
       
        $mergedRows = [];

        foreach ($rows as $index => $row) {
            $barcode = isset($row['bar_code']) ? trim($row['bar_code']) : null;
            $gtin = isset($row['gtin']) ? trim($row['gtin']) : null;
            $batchNumber = isset($row['batch_number']) ? trim($row['batch_number']) : null;

            // Skip if both identifiers are missing or batch number is missing
            if ((empty($barcode) && empty($gtin)) || empty($batchNumber)) {
                continue;
            }

            $data = [
                'bar_code'     => $barcode,
                'gtin'         => $gtin,
                'batch_number' => $batchNumber,
                'stock'        => (int) ($row['stock'] ?? 0),
                'expiry_date'  => $row['expiry_date'] ?? null,
            ];
 
            // Validate data
            $validator = Validator::make($data, [
                'bar_code'     => 'nullable|string|required_without:gtin',
                'gtin'         => 'nullable|string|required_without:bar_code',
                'batch_number' => 'required|string',
                'stock'        => 'required|integer|min:1',
                'expiry_date'  => 'nullable|date',
            ]);
            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 2, // +2 because of header row
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }


            // Find product by barcode or GTIN
            $product = null;
            if ($barcode && isset($productsByBarcode[$barcode])) {
                $product = $productsByBarcode[$barcode];
            } elseif ($gtin && isset($productsByGtin[$gtin])) {
                $product = $productsByGtin[$gtin];
            }

         

            if (!$product) {
                $identifier = $barcode ?: $gtin;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ["المنتج ذو الكود {$identifier} غير موجود."],
                ];
                continue;
            }

            // Create unique key for merging (product_id, batch_number, expiry_date)
            $expiryKey = $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : 'null';
            $uniqueKey = $product->id . '-' . $data['batch_number'] . '-' . $expiryKey;

            // Merge rows with same product, batch, and expiry date
            if (isset($mergedRows[$uniqueKey])) {
                $mergedRows[$uniqueKey]['stock'] += $data['stock'];
            } else {
                $mergedRows[$uniqueKey] = [
                    'warehouse_id' => $this->warehouse->id,
                    'product_id'   => $product->id,
                    'batch_number' => $data['batch_number'],
                    'stock'        => $data['stock'],
                    'expiry_date'  => $data['expiry_date'] ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
                ];
            }
        }

        // Insert or update data for each chunk
        DB::transaction(function () use ($mergedRows) {
            $warehouse = $this->warehouse;

            // Get existing batches in the warehouse
            $existingBatches = WarehouseProductBatch::where('warehouse_id', $warehouse->id)
                ->whereIn('product_id', array_column($mergedRows, 'product_id'))
                ->get(['product_id', 'batch_number', 'expiry_date', 'id', 'stock'])
                ->mapWithKeys(function ($item) {
                    $batchNumber = trim($item->batch_number);
                    $expiryDate = $item->expiry_date ? date('Y-m-d', strtotime($item->expiry_date)) : 'null';
                    return [$item->product_id . '-' . $batchNumber . '-' . $expiryDate => $item];
                });

            $toInsert = [];

            foreach ($mergedRows as $key => $data) {
                if (isset($existingBatches[$key])) {
                    // Update existing batch stock
                    $existingBatches[$key]->increment('stock', $data['stock']);
                } else {
                    // Ensure product is attached to warehouse
                    if (!$warehouse->products()->where('product_id', $data['product_id'])->exists()) {
                        $warehouse->products()->attach($data['product_id'], ['reserved_stock' => 0]);
                    }
                    $toInsert[] = $data;
                }
            }

            // Bulk insert new batches
            if (!empty($toInsert)) {
                WarehouseProductBatch::insert($toInsert);
            }
        });
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}