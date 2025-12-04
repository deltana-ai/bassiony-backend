<?php

namespace App\Services;

use App\Models\PharmacyOrder;
use App\Models\PharmacyOrderItem;
use App\Models\PharmacyCart;
use App\Models\Product;
use App\Models\CompanyOffer;
use App\Models\Warehouse;
use App\Models\WarehouseProductBatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PharmacyOrderService
{
    /**
     * Create order from cart
     *
     * @param array $data
     * @return PharmacyOrder
     * @throws \Exception
     */
    public function createOrderFromCart(array $data): PharmacyOrder
    {
        return DB::transaction(function () use ($data) {
            // Get cart
            $cart = PharmacyCart::with('items.product')
                ->where('pharmacy_id', $data['pharmacy_id'])
                ->where('company_id', $data['company_id'])
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Create the order (without warehouse - will be assigned on approval)
            $order = PharmacyOrder::create([
                'company_id' => $data['company_id'],
                'pharmacy_id' => $data['pharmacy_id'],
                'warehouse_id' => null, // Will be assigned when company approves
                'branch_id' => $data['branch_id'] ?? null,
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            // Process cart items and calculate with offers
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $quantity = $cartItem->quantity;

                // Get active offers for this product
                $offer = CompanyOffer::getActiveOffer($data['company_id'], $product->id, $quantity);

                if ($offer) {
                    $calculatedData = CompanyOffer::calculateOfferPrice($offer, $product, $quantity);
                    
                    // Create order items (including free items)
                    $pharmacy_order_items = [];
                    foreach ($calculatedData['items'] as $orderItem) {
                        $pharmacy_order_items[] = [
                            'pharmacy_order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $orderItem['quantity'],
                            'price' => $orderItem['price'],
                        ];
                    }

                    PharmacyOrderItem::insert($pharmacy_order_items);
                     



                    $totalPrice += $calculatedData['total_price'];
                } else {
                    // No offer - regular pricing
                    $price = $product->producPrice()->where('company_id', $data['company_id'])->first()->sell_price ?? $product->price;
                    
                    PharmacyOrderItem::create([
                        'pharmacy_order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price * $quantity,
                    ]);

                    $totalPrice += ($price * $quantity);
                }
            }

            // Update order total price
            $order->update([
                'total_price' => $totalPrice + ($data['delivery_fee'] ?? 0)
            ]);

            // Clear cart after order creation
            $cart->items()->delete();

            return $order->fresh(['items.product', 'pharmacy', 'company']);
        });
    }

   
    public function cancelOrder(int $orderId)
    {
        return DB::transaction(function () use ($orderId) {

            $order = PharmacyOrder::with('items')->findOrFail($orderId);
            if ($order->warehouse_id!==null) {
                $warehouseId = $order->warehouse_id;
                foreach ($order->items as $item) {
                $this->returnReserveStock($warehouseId, $item->product_id, $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });
    }


  

    /**
     * Approve order and assign warehouse
     */
    public function approveOrder(int $orderId, int $warehouseId): PharmacyOrder
    {
        return DB::transaction(function () use ($orderId, $warehouseId) {
            $order = PharmacyOrder::with('items')->findOrFail($orderId);


            // Validate warehouse belongs to company
            $warehouse = Warehouse::where('id', $warehouseId)
                ->where('company_id', $order->company_id)
                ->firstOrFail();

            // Validate stock availability in the assigned warehouse
            $this->validateStockAvailability($order->items->toArray(), $warehouseId);

            // Reserve stock
            foreach ($order->items as $item) {
                $this->reserveStock($warehouseId, $item->product_id, $item->quantity);
            }

            // Deduct stock from batches (FIFO)
            

            // Update order with warehouse and status
            $order->update([
                'warehouse_id' => $warehouseId,
                'status' => 'approved'
            ]);

            return $order->fresh(['items.product', 'warehouse']);
        });
    }

    public function shippedOrder(int $orderId): PharmacyOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = PharmacyOrder::with('items')->findOrFail($orderId);

            if ($order->warehouse_id!==null) {

                $warehouseId = $order->warehouse_id;

                foreach ($order->items as $item) 
                {
        
                   $this->deductStockFromBatches(
                        $warehouseId,
                        $item->product_id,
                        $item->quantity
                    );
                }
            }
            $order->update(['status' => 'shipped']);
            return $order->fresh();
        });
    }

    /**
     * Validate stock availability
     */
    private function validateStockAvailability(array $items, int $warehouseId): void
    {
        foreach ($items as $item) {
            $productId = is_array($item) ? $item['product_id'] : $item->product_id;
            $quantity = is_array($item) ? $item['quantity'] : $item->quantity;

            $Stock = WarehouseProductBatch::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->sum('stock');

                // Get reserved stock
            $reservedStock = DB::table('warehouse_product')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->value('reserved_stock') ?? 0;

            $availableStock = $Stock - $reservedStock;
            if ($availableStock < $quantity) {
                $product = Product::find($productId);
                throw new \Exception("المخزون غير كافي للمنتج: {$product->name_en}. المتوفر: {$availableStock}, المطلوب: {$quantity}");

            }
            
        }
    }

    /**
     * Reserve stock for order
     */
    private function reserveStock(int $warehouseId, int $productId, int $quantity): void
    {
        DB::table('warehouse_product')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->increment('reserved_stock', $quantity);
    }


     /**
     *  return Reserve stock for order to be available
     */
    private function returnReserveStock(int $warehouseId, int $productId, int $quantity): void
    {
        DB::table('warehouse_product')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->decrement('reserved_stock', $quantity);
    }

    /**
     * Deduct stock from batches using FIFO
     */
    private function deductStockFromBatches(int $warehouseId, int $productId, int $quantity): void
    {
        $remainingQuantity = $quantity;

        $batches = WarehouseProductBatch::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO
            ->get();

        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $deductAmount = min($batch->stock, $remainingQuantity);
            
            $batch->decrement('stock', $deductAmount);
            $remainingQuantity -= $deductAmount;
        }

        // Unreserve stock
        DB::table('warehouse_product')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->decrement('reserved_stock', $quantity);
    }

    /**
     * Reject order
     */
    public function rejectOrder(int $orderId, ?string $reason = null): PharmacyOrder
    {
        return DB::transaction(function () use ($orderId, $reason) {
            $order = PharmacyOrder::findOrFail($orderId);

            if ($order->status !== 'pending') {
                throw new \Exception('Only pending orders can be rejected');
            }

            if ($reason) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') . "Rejection Reason: " . $reason;
            }

            $order->update(['status' => 'rejected']);

            return $order->fresh();
        });
    }

    /**
     * Get company warehouses with stock availability for order
     */
    public function getWarehousesForOrder(int $orderId): array
    {
        $order = PharmacyOrder::with('items.product')->findOrFail($orderId);
        
        $warehouses = DB::table('warehouses')
            ->where('company_id', $order->company_id)
            ->where('active', true)
            ->select(['id', 'name', 'address'])
            ->get();

        $result = [];

        foreach ($warehouses as $warehouse) {
            $canFulfill = true;
            $stockInfo = [];

            foreach ($order->items as $item) {
                $availableStock = WarehouseProductBatch::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $item->product_id)
                    ->sum('stock');

                $reservedStock = DB::table('warehouse_product')
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_id', $item->product_id)
                    ->value('reserved_stock') ?? 0;

                $actualAvailable = $availableStock - $reservedStock;

                $stockInfo[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_en,
                    'required' => $item->quantity,
                    'available' => $actualAvailable,
                    'sufficient' => $actualAvailable >= $item->quantity,
                ];

                if ($actualAvailable < $item->quantity) {
                    $canFulfill = false;
                }
            }

            $result[] = [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'warehouse_address' => $warehouse->address,
                'can_fulfill_order' => $canFulfill,
                'stock_details' => $stockInfo,
            ];
        }

        return $result;
    }

    /**
     * Create return request
     */
    public function createReturnRequest(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = PharmacyOrder::with('items')->findOrFail($data['pharmacy_order_id']);

            if ($order->status !== 'delivered') {
                throw new \Exception('Only delivered orders can be returned');
            }

            // Create return record
            $return = DB::table('pharmacy_order_returns')->insertGetId([
                'pharmacy_id' => $order->pharmacy_id,
                'pharmacy_order_id' => $order->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update order items with return information
            foreach ($data['items'] as $item) {
                $returnNumber = 'RET-' . $order->id . '-' . $item['product_id'] . '-' . time();
                
                PharmacyOrderItem::where('pharmacy_order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->update([
                        'return_number' => $returnNumber,
                        'reason' => $item['reason'] ?? null,
                    ]);
            }

            return [
                'return_id' => $return,
                'order' => $order->fresh('items'),
            ];
        });
    }
}