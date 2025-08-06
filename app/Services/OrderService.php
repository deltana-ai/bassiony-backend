<?php
namespace App\Services;

use App\Models\{Cart, MasterOrder, Order, Shipping, Coupon, Wallet, WalletTransaction, PointSetting, UserAddress};
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function createOrder($user, $request)
    {
        DB::beginTransaction();
        try {
            $cart = $user->cart()->with('items.product.offer')->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception(__('lang.cart_is_empty'));
            }


            foreach ($cart->items as $item) {
                $product = $item->product;

                if (!$product->active) {
                    throw new \Exception( $product->name.__("lang.not available now."));
                }

                if ($item->quantity > $product->quantity) {
                    throw new \Exception(__("lang.your quantity not available for."). $product->name);
                }
            }
            $this->validateAllShippingTypesOrFail($request->requests, $cart);

            $coupon =null;
             if ($request->filled('code')) {
                $coupon =  $this->validateCouponOrFail($request->code);
             }


             $master_order = MasterOrder::create([
                 'user_id' => $user->id,
                 'total' => 0,
                 'status'  => 'pending',
                 'coupon_id'=> $coupon?->id

             ]);


             $groupedItems = $cart->items->groupBy(fn($item) => $item->product->pharmacy_id);

             $orders = [];
             $total =0;
             $final_total=0;
             $all_shipping_value = 0;
             foreach ($groupedItems as $pharmacyId => $items) {

                $pharmacyRequest = $request->requests[$pharmacyId] ?? null;

                if (!$pharmacyRequest) {
                    throw ValidationException::withMessages([
                        'requests.' . $pharmacyId => [__('lang.shipping data not exist for this pharmacy')]
                    ]);
                }

                $shippingType = $pharmacyRequest['shipping_type'];
                $shippingAddress = $pharmacyRequest['shipping_address'];


                 $subtotal = 0;
                 $tax = 0;
                 $itemsData = [];
                 $order_discount =0 ;
                 $order_taxes =0 ;

                 foreach ($items as $item) {
                     $product = $item->product;
                     $quantity = $item->quantity;
                     $basePrice = $product->price;
                     $offer = $product->offer;

                     $discount = 0;
                     $freeQty = 0;
                    $hasOffer = null;
                     if ($offer && $offer->is_active) {
                         if ($offer->type == 'discount') {
                             $discount = $offer->discount_type == 'percentage'
                                 ? $basePrice * $offer->value / 100
                                 : $offer->value;

                         }
                         $hasOffer = $offer;

                     }

                     $finalPrice = max($basePrice - $discount, 0);
                     $taxAmount = $finalPrice * ($product->tax_rate / 100);
                     $lineTotal = ($finalPrice + $taxAmount) * $quantity;
                     $order_discount += $discount * $quantity;
                     $order_taxes += $taxAmount * $quantity;
                     $subtotal += $finalPrice * $quantity;
                     $tax += $taxAmount * $quantity;

                     $itemsData[] = [
                         'product_id'    => $product->id,
                         'price'    => $basePrice,
                         'subtotal'   => $finalPrice,
                         'tax_amount'    => $taxAmount,
                         'quantity'      => $quantity,
                         'discount'      => $discount,
                         'total'         => $lineTotal,
                         'offer_id'  =>$hasOffer?->id
                     ];
                     $product = $item->product;
                     $product->quantity -= $item->quantity;
                     $product->save();
                 }
                 $pointSetting = PointSetting::where('pharmacy_id', $pharmacyId)
                                   ->where('is_active', true)->first();

                 $earnedPoints = 0;

                 if ($pointSetting && $pointSetting->earning_rate > 0) {
                     $earnedPoints = floor($subtotal * $pointSetting->earning_rate);
                 }
                 $order = $master_order->orders()->create([
                     'pharmacy_id'      => $pharmacyId,
                     'status'           => 'pending',
                     'subtotal'         => $subtotal,
                     'tax'              => $tax,
                     'order_discount'   => $order_discount ,
                     'order_taxes'      => $order_taxes ,
                     'total'            => $subtotal + $tax,
                     'final_total'      => $subtotal + $tax,

                     'paid_from_wallet' => 0,
                     'paid_by_card'     => 0,
                     'earned_points'    => $earnedPoints,
                     'is_paid'          => false,
                     'payment_type'     => 'cash',
                     'due_date'         => now()->addDays(7),
                     'paid_amount'      => 0,
                     'remaining_amount' => $subtotal + $tax,
                 ]);
                 $total += $order->total;
                 $final_total += $order->final_total;
                 $order->items()->createMany($itemsData);

                 $this->addEarnedPointsToWallet( $order,$user->id);
                 $shipping_value = $this->applyShippingToOrder($order, $shippingType, $shippingAddress);

                 $orders[] = $order;
                 $all_shipping_value += $shipping_value ;

             }

             $cart->items()->delete();
             $master_order->update(['total' => $total ,'final_total'=>$all_shipping_value+$total]);

             if($request->filled('code')){
               $this->couponApply($request->code, $master_order);
             }


               DB::commit();
               return $master_order;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    /////////////////////////////////////////////////////////////////////////////////

      public function couponApply(string $code, MasterOrder $master_order)
      {
        \Log::info('Applying coupon for master order: ' . $master_order->id);
           $master_order->load(['orders', 'coupon']);
          $master_order->applyCouponDiscount();

      }




      ///////////////////////////////////////////////////////////////////////////////////////////
      public function addEarnedPointsToWallet( $order,$userID)
      {
          if (!$order->earned_points || $order->earned_points <= 0) {
              return;
          }

          try {
              $wallet = Wallet::where('pharmacy_id',$order->pharmacy_id)->where('user_id',$userID)->first();
              if($wallet){
                $wallet->point_balance += $order->earned_points;
                $wallet->save();
              }
              else{
                $wallet = new Wallet;
                $wallet->user_id =  $userID;
                $wallet->pharmacy_id =  $order->pharmacy_id;
                $wallet->balance =  0;
                $wallet->point_balance =  $order->earned_points;
                $wallet->save();
              }


              WalletTransaction::create([
                  'wallet_id' => $wallet->id,
                  'type' => 'earn_points',
                  'points' => $order->earned_points,
                  'amount' => $order->earned_points,
                  'order_id' => $order->id,
                  'description' => __('lang.earned point from order ') . $order->id,
              ]);

          } catch (\Exception $e) {
              throw $e;
          }
      }
       //////////////////////////////////////////////////////////////

     protected function validateCouponOrFail(string $code)
     {
         $coupon = Coupon::where('code', $code)->first();

         if (!$coupon || !$coupon->isValid()) {
             throw ValidationException::withMessages([
                 'code' => [__('lang.the discount code not valid or expired !')]
             ]);
         }

         return $coupon;
     }
    /////////////////////////////////////////////////////////////////////////////
    protected function validateShippingAvailableOrFail(string $shipping_type, int $shipping_address_id, $pharmacyId)
      {



              $shipping = Shipping::where('type', $shipping_type)
                                  ->where('pharmacy_id', $pharmacyId)
                                  ->first();

              if (!$shipping) {
                  throw ValidationException::withMessages([
                      'shipping_type' => [__('lang.this shipping not available for pharmacy ID: ') . $pharmacyId]
                  ]);
              }


          $address = UserAddress::find($shipping_address_id);
          if (!$address) {
              throw ValidationException::withMessages([
                  'shipping_address' => [__('lang.invalid shipping address')]
              ]);
          }

          return true;
      }

      ///////////////////////////////////////////////////////////////////////////////////////
      protected function applyShippingToOrder($order, $shippingType, $shippingAddressId)
      {
          $address = UserAddress::find($shippingAddressId);

          if (!$address) {
              throw ValidationException::withMessages([
                  'shipping_address' => [__('lang.shipping address not found')]
              ]);
          }

          $shipping = Shipping::where('type', $shippingType)
                              ->where('pharmacy_id', $order->pharmacy_id)
                              ->first();

          if (!$shipping) {
              throw ValidationException::withMessages([
                  'shipping_type' => [__('lang.this shipping not available for pharmacy ID: ') . $order->pharmacy_id]
              ]);
          }

          $order->shipping_id = $shipping->id;
          $order->shipping_cost = $shipping->value;
          $order->shipping_address = $address->full_address;
          $order->final_total += $shipping->value;
          $order->remaining_amount += $shipping->value;
          $order->save();

          return  $shipping->value;
      }


      ///////////////////////////////////////////////////////////////////////
      protected function validateAllShippingTypesOrFail(array $requests, $cart)
      {
          if (!$cart || $cart->items->isEmpty()) {
              throw ValidationException::withMessages([
                  'cart' => [__('lang.cart is empty')]
              ]);
          }

          $pharmacyIdsInCart = $cart->items->pluck('product.pharmacy_id')->unique();

          foreach ($pharmacyIdsInCart as $pharmacyId) {
              if (!isset($requests[$pharmacyId])) {
                  throw ValidationException::withMessages([
                      "requests.$pharmacyId" => [__('lang.Missing shipping data for pharmacy ID: ') . $pharmacyId]
                  ]);
              }

              $shipping_type = $requests[$pharmacyId]['shipping_type'] ?? null;
              $shipping_address = $requests[$pharmacyId]['shipping_address'] ?? null;

              if (!$shipping_type) {
                  throw ValidationException::withMessages([
                      "requests.$pharmacyId.shipping_type" => [__('lang.Shipping type is required for pharmacy ID: ') . $pharmacyId]
                  ]);
              }

              if (!$shipping_address || !UserAddress::find($shipping_address)) {
                  throw ValidationException::withMessages([
                      "requests.$pharmacyId.shipping_address" => [__('lang.Invalid shipping address for pharmacy ID: ') . $pharmacyId]
                  ]);
              }

              $shippingExists = Shipping::where('pharmacy_id', $pharmacyId)
                                        ->where('type', $shipping_type)
                                        ->exists();

              if (!$shippingExists) {
                  throw ValidationException::withMessages([
                      "requests.$pharmacyId.shipping_type" => [__('lang.This shipping type is not available for pharmacy ID: ') . $pharmacyId]
                  ]);
              }
          }

          return true;
      }




      }
