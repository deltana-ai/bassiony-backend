<?php
namespace App\Repositories;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductBatch;
use Illuminate\Support\Facades\DB;

class WarehouseStockRepository
{
    /**
     * Get product stock in specific warehouse
     */
    public function getProductStockInWarehouse(int $productId, int $warehouseId): array
    {
        $batches = WarehouseProductBatch::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->get();

        return [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'total_stock' => $batches->sum('stock'),
            'batch_count' => $batches->count(),
            'batches' => $batches->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'stock' => $batch->stock,
                    'expiry_date' => $batch->expiry_date,
                ];
            }),
        ];
    }

    /**
     * Get product stock across all warehouses
     */
    public function getProductStockAllWarehouses(int $productId): array
    {
        $stockByWarehouse = WarehouseProductBatch::where('product_id', $productId)
            ->selectRaw('warehouse_id, SUM(stock) as total_stock, COUNT(*) as batch_count')
            ->with('warehouse:id,name,location')
            ->groupBy('warehouse_id')
            ->get();

        return [
            'product_id' => $productId,
            'total_stock' => $stockByWarehouse->sum('total_stock'),
            'warehouses' => $stockByWarehouse->map(function ($item) {
                return [
                    'warehouse_id' => $item->warehouse_id,
                    'warehouse_name' => $item->warehouse->name,
                    'location' => $item->warehouse->location,
                    'stock' => $item->total_stock,
                    'batch_count' => $item->batch_count,
                ];
            }),
        ];
    }

    /**
     * Get available stock (total - reserved)
     */
    public function getAvailableStock(int $productId, int $warehouseId): array
    {
        $totalStock = WarehouseProductBatch::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->sum('stock');

        $reservedStock = DB::table('warehouse_product')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('reserved_stock') ?? 0;

        return [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'total_stock' => $totalStock,
            'reserved_stock' => $reservedStock,
            'available_stock' => $totalStock - $reservedStock,
        ];
    }

    /**
     * Get stock with expiry information
     */
    public function getStockWithExpiry(int $productId, int $warehouseId): array
    {
        $batches = WarehouseProductBatch::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('expiry_date')
            ->get();

        $now = now();

        return [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'total_stock' => $batches->sum('stock'),
            'expired_stock' => $batches->filter(function ($batch) use ($now) {
                return $batch->expiry_date && $batch->expiry_date < $now;
            })->sum('stock'),
            'expiring_soon_stock' => $batches->filter(function ($batch) use ($now) {
                return $batch->expiry_date && 
                       $batch->expiry_date >= $now && 
                       $batch->expiry_date <= $now->copy()->addMonths(3);
            })->sum('stock'),
            'batches' => $batches,
        ];
    }

    /**
     * Get low stock products in warehouse
     */
    public function getLowStockProducts(int $warehouseId, int $threshold = 10): array
    {
        return WarehouseProductBatch::where('warehouse_id', $warehouseId)
            ->selectRaw('product_id, SUM(stock) as total_stock')
            ->with('product:id,name,price')
            ->groupBy('product_id')
            ->havingRaw('SUM(stock) < ?', [$threshold])
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'stock' => $item->total_stock,
                    'status' => 'low_stock',
                ];
            })
            ->toArray();
    }


    public function getReportForWarehouseStock()
    {

        // Get detailed stock report
        $stockReport = DB::table('warehouse_product_batches as wpb')
            ->join('products as p', 'wpb.product_id', '=', 'p.id')
            ->join('warehouses as w', 'wpb.warehouse_id', '=', 'w.id')
            ->leftJoin('warehouse_product as wp', function ($join) {
                $join->on('wpb.product_id', '=', 'wp.product_id')
                    ->on('wpb.warehouse_id', '=', 'wp.warehouse_id');
            })
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'w.id as warehouse_id',
                'w.name as warehouse_name',
                DB::raw('SUM(wpb.stock) as total_stock'),
                DB::raw('COALESCE(wp.reserved_stock, 0) as reserved_stock'),
                DB::raw('SUM(wpb.stock) - COALESCE(wp.reserved_stock, 0) as available_stock'),
                DB::raw('COUNT(wpb.id) as batch_count')
            )
            ->where('wpb.product_id', 1)
            ->where('wpb.warehouse_id', 2)
            ->groupBy('p.id', 'p.name', 'w.id', 'w.name', 'wp.reserved_stock')
            ->first();
        return $stockReport;
    }


    
}