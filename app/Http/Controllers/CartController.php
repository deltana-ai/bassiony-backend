<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Resources\CartItemResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CartItem;
use Exception;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
        $cartItems = $request->user()
            ->cartItems()
            ->with('product.category', 'product.brand', 'product.media') // eager loading
            ->get();

        return response()->json([
            'data'    => CartItemResource::collection($cartItems),
            'result'  => "Success",
            'message' => 'Cart items fetched successfully',
            'status'  => 200,
        ], 200);

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = CartItem::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'product_id' => $validated['product_id'],
                ],
                [
                  'quantity' => $validated['quantity'],
                ]
            );
         $cartItem->refresh();

            return response()->json([
                'data' => $cartItem,
                'result'=> "Success",
                'message' => 'Item added to cart successfully',
                'status' => 200,
            ], 200);

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function update(Request $request, CartItem $cartItem)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem->update([
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'data' => $cartItem ,
                'result'=> "Success",
                'message' => 'Cart item updated successfully',
                'status' => 200,
            ], 200);

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function destroy(CartItem $cartItem)
    {
        try {
            $cartItem->delete();
            return response()->json([
                'data' => null,
                'result'=> "Success",
                'message' => 'Cart item deleted successfully',
                'status' => 200,
            ], 200);

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
