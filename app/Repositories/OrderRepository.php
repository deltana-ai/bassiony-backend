<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\{CartItem, OrderItem, PromoCode, Order};
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    private Order $model;

    public function __construct(Order $order)
    {
        $this->model = $order;
    }

    public function createOrder(array $data, $user)
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            throw new \Exception("Cart is empty");
        }

        return DB::transaction(function () use ($user, $cartItems, $data) {
            $deliveryFee   = $data['delivery_fee'] ?? 0;
            $promoDiscount = $this->calculatePromoDiscount($cartItems, $data['promo_code_id'] ?? null);

            $order = $this->model->create([
                'user_id'       => $user->id,
                'address_id'    => $data['address_id'] ?? null,
                'promo_code_id' => $data['promo_code_id'] ?? null,
                'payment_method'=> $data['payment_method'] ?? 'cash',
                'status'        => 'pending',
                'delivery_fee'  => $deliveryFee,
            ]);

            $productsTotal = $this->storeOrderItems($order->id, $cartItems);

            $tax = $this->calculateTax($productsTotal);
            $finalPrice = $productsTotal + $tax + $deliveryFee - $promoDiscount;

            $order->update([
                'total_price' => $finalPrice,
            ]);

            CartItem::where('user_id', $user->id)->delete();

            return $order->load('items.product');
        });
    }

    private function calculatePromoDiscount($cartItems, $promoCodeId)
    {
        if (!$promoCodeId) return 0;

        $promo = PromoCode::find($promoCodeId);
        if (!$promo || !$promo->is_active) return 0;

        $productsTotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        return $promo->discount_type === 'percentage'
            ? ($productsTotal * $promo->discount_value) / 100
            : $promo->discount_value;
    }

    private function calculateTax($productsTotal)
    {
        return $productsTotal * 0.15;
    }

    private function storeOrderItems($orderId, $cartItems)
    {
        $productsTotal = 0;

        foreach ($cartItems as $item) {
            $price = $item->product->price;
            $total = $price * $item->quantity;

            OrderItem::create([
                'order_id'   => $orderId,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $price,
                'total'      => $total,
            ]);

            $productsTotal += $total;
        }

        return $productsTotal;
    }
}
