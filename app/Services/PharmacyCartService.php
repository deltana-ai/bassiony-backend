<?php

namespace App\Services;

use App\Models\CompanyOffer;
use App\Models\PharmacyCart;
use App\Models\PharmacyCartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PharmacyCartService
{
    /**
     * Get or create cart for pharmacy and company
     */
    public function getCart(int $pharmacyId, int $companyId): PharmacyCart
    {
        return PharmacyCart::firstOrCreate(
            [
                'pharmacy_id' => $pharmacyId,
                'company_id' => $companyId,
            ]
        );
    }

    /**
     * Add item to cart
     */
    public function addItem(int $pharmacyId, int $companyId, int $productId, int $quantity = 1): PharmacyCartItem
    {
        // Validate product exists and belongs to company
        $product = Product::whereHas('warehouses', function ($query) use ($companyId) {
            $query->whereHas('company', function ($q) use ($companyId) {
                $q->where('id', $companyId);
            });
        })->findOrFail($productId);

        $cart = $this->getCart($pharmacyId, $companyId);

        // Check if item already exists in cart
        $cartItem = PharmacyCartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->increment('quantity', $quantity);
            return $cartItem->fresh();
        }

        // Create new cart item
        return PharmacyCartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItemQuantity(int $cartItemId, int $quantity): PharmacyCartItem
    {
        if ($quantity < 1) {
            throw new \Exception('Quantity must be at least 1');
        }

        $cartItem = PharmacyCartItem::findOrFail($cartItemId);
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh();
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): bool
    {
        $cartItem = PharmacyCartItem::findOrFail($cartItemId);
        return $cartItem->delete();
    }

    /**
     * Clear entire cart
     */
    public function clearCart(int $pharmacyId, int $companyId): bool
    {
        $cart = PharmacyCart::where('pharmacy_id', $pharmacyId)
            ->where('company_id', $companyId)
            ->first();

        if ($cart) {
            $cart->items()->delete();
            return true;
        }

        return false;
    }

    /**
     * Get cart with items
     */
    public function getCartWithItems(int $pharmacyId, int $companyId): ?PharmacyCart
    {
        return PharmacyCart::with(['items.product.media', 'items.product.category', 'items.product.brand'])
            ->where('pharmacy_id', $pharmacyId)
            ->where('company_id', $companyId)
            ->first();
    }

    
}