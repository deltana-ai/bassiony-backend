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
use Illuminate\Support\Facades\DB;

class PharmacyOrderController extends BaseController
{
     // 🛒 عرض الكارت
    public function index(Request $request)
    {
        $pharmacyId = $request->pharmacy_id;

        $items = CartItem::where('pharmacy_id', $pharmacyId)
            ->with('product:id,name,price')
            ->get();

        return CartItemResource::collection($items);
    }

    // ➕ إضافة أو تحديث منتج في الكارت
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::updateOrCreate(
            [
                'pharmacy_id' => $validated['pharmacy_id'],
                'product_id' => $validated['product_id'],
            ],
            ['quantity' => $validated['quantity']]
        );

        return response()->json(['message' => 'تمت الإضافة للسلة بنجاح', 'item' => $item]);
    }

    // ❌ حذف منتج من الكارت
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'product_id' => 'required|exists:products,id',
        ]);

        CartItem::where('pharmacy_id', $validated['pharmacy_id'])
            ->where('product_id', $validated['product_id'])
            ->delete();

        return response()->json(['message' => 'تم حذف المنتج من السلة']);
    }

      public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        $pharmacyId = $validated['pharmacy_id'];

        $cartItems = CartItem::where('pharmacy_id', $pharmacyId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة'], 400);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'pharmacy_id' => $pharmacyId,
                'status' => 'pending',
                'payment_method' => 'cash',
                'total_price' => 0,
            ]);

            $total = 0;

            foreach ($cartItems as $item) {
                $price = $item->product->price ?? 0;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);
                $total += $price * $item->quantity;
            }

            $order->update(['total_price' => $total]);

            // تفريغ الكارت بعد إنشاء الأوردر
            CartItem::where('pharmacy_id', $pharmacyId)->delete();

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الأوردر بنجاح',
                'order_id' => $order->id,
                'total_price' => $total,
                'status' => 'pending',
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الأوردر',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
