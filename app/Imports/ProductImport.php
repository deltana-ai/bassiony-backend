<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Row;

class ProductsImport implements OnEachRow, WithHeadingRow, WithChunkReading, SkipsEmptyRows, ShouldQueue
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['name']) || empty($data['bar_code'])) {
            return;
        }

        try {
            Product::upsert(
                [[
                    'bar_code' => $data['bar_code'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? '',
                    'price' => $data['price'] ?? 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]],
                ['bar_code'],
                ['name', 'description', 'price', 'updated_at']
            );
        } catch (\Exception $e) {
            Log::error("خطأ في الصف {$row->getIndex()}: {$e->getMessage()}");
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
