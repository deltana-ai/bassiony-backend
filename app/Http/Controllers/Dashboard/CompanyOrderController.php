<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ProductResource;
use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyOrderController extends BaseController
{
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'result' => 'Error',
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'result' => 'Error',
                'message' => 'Only pending orders can be updated',
            ], 400);
        }

        $order->status = $request->status;

         if ($request->status === 'rejected' && $request->filled('reason')) {
            $order->review = $request->reason;
        }

        $order->save();

        return response()->json([
            'result' => 'Success',
            'message' => "Order {$request->status} successfully",
            'data' => $order,
        ]);
    }




    public function assignWarehouse(Request $request, $id)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'result' => 'Error',
                'message' => 'Order not found',
            ], 404);
        }

        // لازم يكون الأوردر متوافق عليه
        if ($order->status !== 'approved') {
            return response()->json([
                'result' => 'Error',
                'message' => 'Order must be approved before assigning to a warehouse',
            ], 400);
        }

        $warehouse = Warehouse::find($request->warehouse_id);

        // نتأكد إن المخزن تابع لنفس الشركة
        // (لو عندك علاقة بين المنتج والشركة، ممكن نربطها بشكل أقوى)
        if (!$warehouse->active) {
            return response()->json([
                'result' => 'Error',
                'message' => 'This warehouse is inactive',
            ], 400);
        }

        $order->warehouse_id = $warehouse->id;
        $order->save();

        return response()->json([
            'result' => 'Success',
            'message' => 'Order assigned to warehouse successfully',
            'data' => [
                'order_id' => $order->id,
                'warehouse' => $warehouse->name,
            ],
        ]);
    }
}
