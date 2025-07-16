<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order,OrderItem};
use Illuminate\Support\Facades\DB;
use App\Helpers\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Common\OrderResource;

use App\Interfaces\Common\OrderRepositoryInterface;

use App\Repositories\Common\OrderRepository;

class OrderController extends Controller
{

  protected mixed $crudRepository;

  public function __construct(OrderRepositoryInterface $pattern)
  {
      $this->crudRepository = $pattern;
      
  }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }




    /////////////////////////////////////////////////////////////
    /*
    * place order form cart
    */

    public function placeOrder(Request $request)
    {

        $request->validate([
          'payment_type' => 'required',
          'payment_method' => 'required',
        ]);
        $user = auth('client')->user();

        try {
            DB::beginTransaction();

            $cart = $user->cart()->with([
                'items.pharmacyProduct.pharmacy',
                'items.pharmacyProduct.offer'
            ])->first();

            if (!$cart || $cart->items->isEmpty()) {
                return JsonResponse::respondError('Cart Empty');
            }

            $grouped = $cart->groupCartItemsByPharmacy();

            foreach ($grouped as $group) {
              $orderTotal = 0;
              $totalDiscount = 0;

              foreach ($group->items as $item) {
                  $product = $item->pharmacyProduct;
                  $product->quantity -=$item->quantity;
                  $product->save();
                  $originalPrice = $product->price;
                  $discountedPrice = $product->priceAfterOffer();
                  $quantity = $item->quantity;

                  $itemTotal = bcmul($discountedPrice, $quantity, 2);
                  $discountValue = bcmul($originalPrice - $discountedPrice, $quantity, 2);

                  $orderTotal += $itemTotal;
                  $totalDiscount += $discountValue;
              }

            //  $pointResult = $this->applyPointsToOrderForUser($user, $orderTotal);

              $order = Order::create([
                  'user_id' => $user->id,
                  'pharmacy_id' => $group->pharmacy_id,
                  'order_discount' => $totalDiscount,
                  'paid_with_points' => 0, //$pointResult['used_amount'],
                  'paid_with_money' => 0, //will be change
                  'is_paid' => false,      //$pointResult['payable'] == 0,
                  'payment_type' => $request->payment_type,
                  'payment_method' => $request->payment_method,
                  'paid_amount' => 0, //$pointResult['used_amount'],
                  'total' =>$orderTotal,
                  'status' => 'pending',
              ]);

              foreach ($group->items as $item) {

                  $product = $item->pharmacyProduct;

                  $discountedPrice = $product->priceAfterOffer();
                  $quantity = $item->quantity;
                  $itemTotal = bcmul($discountedPrice, $quantity, 2);
                  $discountValue = bcmul($product->price - $discountedPrice, $quantity, 2);

                  OrderItem::create([
                      'order_id' => $order->id,
                      'product_id' => $product->id,
                      'unit_price' => $discountedPrice,
                      'discount' => $discountValue,
                      'subtotal' => $itemTotal,
                      'quantity' => $quantity,
                      'total' => $itemTotal,
                  ]);
              }
          }


            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return JsonResponse::respondSuccess('Order Saved Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }






    //////////////////////////////////////////////////////////////////////
    /**
   *
   */
  private function applyPointsToOrderForUser($user, float $orderTotal, $order = null, float $pointValue = 1)
  {
      $now = now();

      $earned = $user->points()
          ->where('type', 'earned')
          ->where(function ($q) use ($now) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
          })->sum('amount');

      $spent = $user->points()->where('type', 'spent')->sum('amount');

      $expired = $user->points()
          ->where('type', 'earned')
          ->whereNotNull('expires_at')
          ->where('expires_at', '<=', $now)
          ->sum('amount');

      $availablePoints = max(0, $earned - $spent - $expired);
      $availableAmount = bcmul($availablePoints, $pointValue, 2);

      $usedAmount = min($availableAmount, $orderTotal);
      $usedPoints = bcdiv($usedAmount, $pointValue, 0);
      $payable = bcsub($orderTotal, $usedAmount, 2);

      if ($usedPoints > 0 && $order) {
          $user->points()->create([
              'type' => 'spent',
              'amount' => $usedPoints,
              // 'source_name' => "ordernum{$order->id}",
              'expires_at' => null
          ]);
      }

      return [
          'used_amount' => (float) $usedAmount,
          'used_points' => (int) $usedPoints,
          'payable' => (float) $payable,
      ];
  }


}
