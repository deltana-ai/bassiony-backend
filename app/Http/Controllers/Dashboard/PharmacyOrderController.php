<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\PharmacyRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\Dashboard\PharmacyResource;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyOrderController extends BaseController
{
     // 🛒 عرض الكارت
    // public function index(Request $request)
    // {
    //     $pharmacyId = $request->pharmacy_id;

    //     $items = CartItem::where('pharmacy_id', $pharmacyId)
    //         ->with('product:id,name,price')
    //         ->get();

    //     return CartItemResource::collection($items);
    // }

    public function index(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $pharmacyId = $request->pharmacy_id;

            $items = CartItem::where('pharmacy_id', $pharmacyId)
                ->with('product:id,name,price')
                ->get();

            return CartItemResource::collection($items)
                ->additional(JsonResponse::success())
                ->response();
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }



    // ➕ إضافة أو تحديث منتج في الكارت
    public function store(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'pharmacy_id' => 'required|exists:pharmacies,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $item = CartItem::updateOrCreate(
                [
                    'pharmacy_id' => $validated['pharmacy_id'],
                    'product_id'  => $validated['product_id'],
                ],
                ['quantity' => $validated['quantity']]
            );

            return (new CartItemResource($item))
                ->additional(JsonResponse::success('تمت الإضافة للسلة بنجاح'))
                ->response();
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    // ❌ حذف منتج من الكارت
    public function destroy(Request $request)
    {
        try {
        $validated = $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'product_id' => 'required|exists:products,id',
        ]);

        CartItem::where('pharmacy_id', $validated['pharmacy_id'])
            ->where('product_id', $validated['product_id'])
            ->delete();
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function storeOrder(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {

            $validated = $request->validate([
                'pharmacy_id' => 'required|exists:pharmacies,id',
                'warehouse_id' => 'required|exists:warehouses,id',
            ]);

            $pharmacyId = $validated['pharmacy_id'];

            $cartItems = CartItem::where('pharmacy_id', $pharmacyId)
                ->with('product')
                ->get();

            if ($cartItems->isEmpty()) {
                return JsonResponse::respondError('السلة فارغة', 400);
            }

            DB::beginTransaction();

            $order = Order::create([
                'pharmacy_id'   => $pharmacyId,
                'pharmacist_id' => auth()->user()->id,
                'warehouse_id'  => $validated['warehouse_id'], // ✅ أضفنا المخزن
                'status'        => 'pending',
                'payment_method'=> 'cash',
                'total_price'   => 0,
            ]);

            $total = 0;

            foreach ($cartItems as $item) {
                $price = $item->product->price ?? 0;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $price,
                ]);

                $total += $price * $item->quantity;
            }

            $order->update(['total_price' => $total]);

            CartItem::where('pharmacy_id', $pharmacyId)->delete();

            DB::commit();

            return JsonResponse::respondSuccess(
                'تم إنشاء الأوردر بنجاح',
                [
                    'order_id'    => $order->id,
                    'total_price' => $total,
                    'warehouse_id'=> $validated['warehouse_id'],
                    'status'      => 'pending',
                ],
                200
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            return JsonResponse::respondError(
                'حدث خطأ أثناء إنشاء الأوردر: ' . $e->getMessage(),
                500
            );
        }
    }


}
