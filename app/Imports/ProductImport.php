<?php

namespace App\Imports;

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
            ];

            $validator = Validator::make($data, [
                'name'     => 'required|string|max:255',
                'bar_code' => 'required|string|unique:products,bar_code',
                'price'    => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $key = $data['bar_code'];
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
}
