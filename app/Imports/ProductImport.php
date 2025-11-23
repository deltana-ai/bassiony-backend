<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProductImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithChunkReading, SkipsEmptyRows
{
    use SkipsFailures;

    protected $errors = [];

    public function collection(Collection $rows)
    {
        // Get default category once
        $category = Category::firstOrCreate(['name' => "فئة مجهولة"]);
        
        // Get existing products to check for duplicates
        $barcodes = $rows->pluck('barcode')->filter()->map(fn($v) => trim($v))->unique()->toArray();
        $gtins = $rows->pluck('gtin')->filter()->map(fn($v) => trim($v))->unique()->toArray();
        
        $existingBarcodes = Product::whereIn('bar_code', $barcodes)->pluck('bar_code')->toArray();
        $existingGtins = Product::whereIn('gtin', $gtins)->pluck('gtin')->toArray();

        $mergedRows = [];
        $rowMapping = []; // لتتبع رقم الصف الأصلي

        foreach ($rows as $index => $row) {
            // Check if at least one identifier exists
            $barcode = isset($row['barcode']) ? trim($row['barcode']) : null;
            $gtin = isset($row['gtin']) ? trim($row['gtin']) : null;
            $nameEn = isset($row['name_en']) ? trim($row['name_en']) : null;
            $nameAr = isset($row['name_ar']) ? trim($row['name_ar']) : null;
            $price = isset($row['price']) ? $row['price'] : null;

            // Skip if missing required fields
            if ((empty($barcode) && empty($gtin)) || (empty($nameEn) && empty($nameAr)) || empty($price)) {
                continue;
            }

            // Check if product already exists in database
            if (($barcode && in_array($barcode, $existingBarcodes)) || 
                ($gtin && in_array($gtin, $existingGtins))) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'bar_code' => $barcode,
                    'gtin' => $gtin,
                    'name' => $nameEn ?: $nameAr,
                    'errors' => ['المنتج موجود بالفعل في قاعدة البيانات'],
                ];
                continue;
            }

            $data = [
                'name_en'     => $nameEn,
                'name_ar'     => $nameAr,
                'bar_code'    => $barcode,
                'gtin'        => $gtin,
                'dosage_form' => isset($row['dosageform']) ? trim($row['dosageform']) : null,
                'scientific_name' => isset($row['scientificname']) ? trim($row['scientificname']) : null,
                'active_ingredients' => isset($row['activeingredients']) ? trim($row['activeingredients']) : null,
                'description' => isset($row['description']) ? trim($row['description']) : null,
                'active'      => isset($row['active']) ? (bool) $row['active'] : true,
                'price'       => (float) $price,
                'category_id' => $category->id,
            ];

            // Validate data
            $validator = Validator::make($data, [
                'name_en'     => 'nullable|string|max:255',
                'name_ar'     => 'nullable|string|max:255',
                'gtin'        => 'nullable|string|required_without:bar_code',
                'bar_code'    => 'nullable|string|required_without:gtin',
                'dosage_form' => 'nullable|string|max:225',
                'scientific_name' => 'nullable|string|max:255',
                'active_ingredients' => 'nullable|string|max:1000',
                'description' => 'nullable|string|max:1000',
                'active'      => 'boolean',
                'price'       => 'required|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'bar_code' => $barcode,
                    'gtin' => $gtin,
                    'name' => $nameEn ?: $nameAr,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Create unique key using both barcode and gtin
            $key = ($barcode ?: 'no_barcode') . '-' . ($gtin ?: 'no_gtin');

            // Prevent duplicates within the same chunk
            if (!isset($mergedRows[$key])) {
                $mergedRows[$key] = $data;
                $rowMapping[$key] = $index + 2; // حفظ رقم الصف
            }
        }

        // Insert each row individually to avoid rollback of valid rows
        if (!empty($mergedRows)) {
            $timestamp = now();
            
            foreach ($mergedRows as $key => $data) {
                try {
                    DB::transaction(function () use ($data, $timestamp) {
                        $data['created_at'] = $timestamp;
                        $data['updated_at'] = $timestamp;
                        Product::create($data);
                    });
                } catch (\Exception $e) {
                    // Log error for this specific row
                    $this->errors[] = [
                        'row' => $rowMapping[$key] ?? 'unknown',
                        'bar_code' => $data['bar_code'] ?? null,
                        'gtin' => $data['gtin'] ?? null,
                        'name' => $data['name_en'] ?: $data['name_ar'],
                        'errors' => ['فشل في قاعدة البيانات: ' . $e->getMessage()],
                    ];
                }
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrorsCount()
    {
        return count($this->errors);
    }

    public function getErrorsSummary()
    {
        return [
            'total_errors' => count($this->errors),
            'errors' => $this->errors
        ];
    }
}