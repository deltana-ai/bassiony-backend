<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\OrderPharmacistResource;
use App\Http\Resources\OrderResource;
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
    public function updateStatus(Request $request, $id): ?\Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected',
                'reason' => 'nullable|string|max:500',
            ]);

            $order = Order::find($id);

            if (!$order) {
                return JsonResponse::respondError('Order not found', 404);
            }

            if ($order->status !== 'pending') {
                return JsonResponse::respondError('Only pending orders can be updated', 400);
            }

            $order->status = $request->status;

            if ($request->status === 'rejected' && $request->filled('reason')) {
                $order->review = $request->reason;
            }

            $order->save();

            return JsonResponse::respondSuccess(
                "Order {$request->status} successfully",
                new OrderResource($order)
            );
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 500);
        }
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
            'data' => [
                'order_id' => $order->id,
                'warehouse' => $warehouse->name,
            ],
            'message' => 'Order assigned to warehouse successfully',
            'status' => '200',
        ]);
    }



    public function getAllPharmacyOrders()
    {
        $orders = Order::whereNotNull('pharmacy_id')
            ->with(['user', 'pharmacist', 'promoCode', 'address'])
            ->orderBy('id', 'desc')
            ->get();

        return JsonResponse::respondSuccess('Orders Fetched Successfully', OrderPharmacistResource::collection($orders));
    }
}
