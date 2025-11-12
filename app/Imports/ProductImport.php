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
                'name_en'     => trim($row['Name_en']),
                'name_ar'     => trim($row['Name_ar']),
                'bar_code' => trim($row['BarCode']),
                'gtin' => trim($row['GTIN']),
                'dosage_form' => trim($row['DosageForm']),
                'scientific_name' => trim($row['ScientificName']),
                'active_ingredients' => trim($row['ActiveIngredients']),
                'description' => trim($row['Description']),
                'active' => (bool) ($row['Active'] ?? false),
                'price'    => (float) ($row['Price'] ?? 0),
            ];
            

            $validator = Validator::make($data, [
                'name_en'     => 'nullable|string|max:255',
                'name_ar'     => 'nullable|string|max:255',
                'gtin' => 'nullable|string|required_without:bar_code|unique:products,gtin',
                'bar_code' => 'nullable|string|required_without:gtin|unique:products,bar_code',
                'dosage_form' => 'nullable|string|max:225',
                'scientific_name' => 'nullable|string|max:255',
                'active_ingredients' => 'nullable|string|max:1000',
                'description' => 'nullable|string|max:1000',
                'active' => 'nullable|boolean',
                'price'    => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                 
                continue;
            }

            $category = Category::firstOrCreate(['name' => "فئة مجهولة"]);

            $key = $data['bar_code'];
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
