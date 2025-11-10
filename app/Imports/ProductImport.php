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
        $mergedRows = [];

        foreach ($rows as $index => $row) {
            if (empty($row['bar_code']) || empty($row['name']) || empty($row['price'])) {
                continue;
            }
           
            $data = [
                'name'     => trim($row['name']),
                'bar_code' => trim($row['bar_code']),
                'description' => $row['description'] ?? null,
                'price'    => (float) ($row['price'] ?? 0),
                'brand_name' => trim($row['brand_name']),
                'category_name' => trim($row['category_name']),
                'active' => filter_var($row['active'], FILTER_VALIDATE_BOOLEAN),
                'show_home' => filter_var($row['show_home'], FILTER_VALIDATE_BOOLEAN),
                'position' => $row['position'] 
            ];
            

            $validator = Validator::make($data, [
                'name'     => 'required|string|max:255',
                'bar_code' => 'required|string|unique:products,bar_code',
                'price'    => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'brand_name' => 'required|string|max:255',
                'category_name' => 'required|string|max:255',
                'active' => 'required|boolean',
                'show_home' => 'required|boolean',
                'position' => 'required|integer|min:1',
            ]);
            
            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                 
                continue;
            }

            $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
            $category = Category::firstOrCreate(['name' => $data['category_name']]);

            $key = $data['bar_code'];
            unset($data['category_name']);
            unset($data['brand_name']);
            $data['brand_id'] = $brand->id;
            $data['category_id'] = $category->id;
          // dd($data);
            if (isset($mergedRows[$key])) {
              
            } else {
                $mergedRows[$key] = $data;
            }
        }

        // Bulk Insert لكل Chunk
        if (!empty($mergedRows)) {
            DB::transaction(function () use ($mergedRows) {
                Product::insert(array_values($mergedRows));
            });
        }
    }

    public function chunkSize(): int
    {
        return 500; // حجم الـ Chunk
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
