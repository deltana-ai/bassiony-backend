<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
  protected $table = 'carts';
  protected $guarded = ['id'];

  public function countCart(){
    return $this->items ? $this->items->count() : 0;

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
              return $item->quantity * $item->final_price;
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
