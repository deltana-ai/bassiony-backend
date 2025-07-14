<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends BaseModel
{
  protected $table = 'carts';

  public function countCart(){
    $item_count=  $this->cartItems->count();
    return $item_count;
  }
  public function groupCartItemsByPharmacy()
  {
      $items = $this->items()
          ->with(['pharmacyProduct.pharmacy', 'pharmacyProduct.offer'])
          ->get();

      return $items->groupBy(function ($item) {
          return $item->pharmacyProduct->pharmacy_id;
      })->map(function ($items, $pharmacyId) {
          $pharmacyName = $items->first()->pharmacyProduct->pharmacy->name ?? 'unknown';

          $subtotal = $items->sum(function ($item) {
              return $item->quantity * $item->pharmacyProduct->priceAfterOffer();
          });

          return (object) [
              'pharmacy_id' => $pharmacyId,
              'pharmacy_name' => $pharmacyName,
              'subtotal' => $subtotal,
              'items' => $items
          ];
      })->values();
  }


  public function getSubtotalAttribute(){

     $sum = $this->items->sum(function($item){
        return $item->total;
     });

     return  $sum;
  }


  public function items()
  {
      return $this->hasMany(CartItem::class,'cart_id');
  }

}
