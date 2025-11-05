<?php
namespace App\Repositories;

use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProductBatch;
use Illuminate\Support\Facades\DB;

class BranchStockRepository
{
    /**
     * Get product stock in specific branch
     */
    public function getProductStockInWarehouse(int $productId, int $branchId): array
    {
        $batches = BranchProductBatch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->get();

        return [
            'product_id' => $productId,
            'branch_id' => $branchId,
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
     * Get product stock across all branches
     */
    public function getProductStockAllWarehouses(int $productId): array
    {
        $stockByWarehouse = BranchProductBatch::where('product_id', $productId)
            ->selectRaw('branch_id, SUM(stock) as total_stock, COUNT(*) as batch_count')
            ->with('branch:id,name,location')
            ->groupBy('branch_id')
            ->get();

        return [
            'product_id' => $productId,
            'total_stock' => $stockByWarehouse->sum('total_stock'),
            'branches' => $stockByWarehouse->map(function ($item) {
                return [
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch->name,
                    'location' => $item->branch->location,
                    'stock' => $item->total_stock,
                    'batch_count' => $item->batch_count,
                ];
            }),
        ];
    }

    /**
     * Get available stock (total - reserved)
     */
    public function getAvailableStock(int $productId, int $branchId): array
    {
        $totalStock = BranchProductBatch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->sum('stock');

        $reservedStock = DB::table('branch_product')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->value('reserved_stock') ?? 0;

        return [
            'product_id' => $productId,
            'branch_id' => $branchId,
            'total_stock' => $totalStock,
            'reserved_stock' => $reservedStock,
            'available_stock' => $totalStock - $reservedStock,
        ];
    }

    /**
     * Get stock with expiry information
     */
    public function getStockWithExpiry(int $productId, int $branchId): array
    {
        $batches = BranchProductBatch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->orderBy('expiry_date')
            ->get();

        $now = now();

        return [
            'product_id' => $productId,
            'branch_id' => $branchId,
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
     * Get low stock products in branch
     */
    public function getLowStockProducts(int $branchId, int $threshold = 10): array
    {
        return BranchProductBatch::where('branch_id', $branchId)
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
        $stockReport = DB::table('branch_product_batches as wpb')
            ->join('products as p', 'wpb.product_id', '=', 'p.id')
            ->join('branches as w', 'wpb.branch_id', '=', 'w.id')
            ->leftJoin('branch_product as wp', function ($join) {
                $join->on('wpb.product_id', '=', 'wp.product_id')
                    ->on('wpb.branch_id', '=', 'wp.branch_id');
            })
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'w.id as branch_id',
                'w.name as branch_name',
                DB::raw('SUM(wpb.stock) as total_stock'),
                DB::raw('COALESCE(wp.reserved_stock, 0) as reserved_stock'),
                DB::raw('SUM(wpb.stock) - COALESCE(wp.reserved_stock, 0) as available_stock'),
                DB::raw('COUNT(wpb.id) as batch_count')
            )
            ->where('wpb.product_id', 1)
            ->where('wpb.branch_id', 2)
            ->groupBy('p.id', 'p.name', 'w.id', 'w.name', 'wp.reserved_stock')
            ->first();
        return $stockReport;
    }
}