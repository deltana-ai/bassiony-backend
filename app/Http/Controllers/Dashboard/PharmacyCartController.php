<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyCartResource;
use App\Services\PharmacyCartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PharmacyCartController extends Controller
{
    protected PharmacyCartService $cartService;

    public function __construct(PharmacyCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart with items
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $summary = $this->getCartSummary(
                $request->pharmacy_id,
                $request->company_id
            );

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'company_id' => 'required|exists:companies,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cartItem = $this->cartService->addItem(
                $request->pharmacy_id,
                $request->company_id,
                $request->product_id,
                $request->quantity
            );

            $summary = $this->getCartSummary(
                $request->pharmacy_id,
                $request->company_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_item' => $cartItem,
                    'cart_summary' => $summary
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, int $cartItemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cartItem = $this->cartService->updateItemQuantity(
                $cartItemId,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $cartItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): JsonResponse
    {
        try {
            $this->cartService->removeItem($cartItemId);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear entire cart
     */
    public function clearCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->cartService->clearCart(
                $request->pharmacy_id,
                $request->company_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Get cart summary
     */
    public function getCartSummary(int $pharmacyId, int $companyId)
    {
        $cart = $this->getCartWithItems($pharmacyId, $companyId);

        if (!$cart) {
            return [
                'total_items' => 0,
                'total_price' => 0,
                'items' => [],
            ];
        }

        return new CompanyCartResource($cart);
       
    }
}