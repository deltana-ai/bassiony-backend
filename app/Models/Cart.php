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

  public function getSubtotalAttribute(){

     $sum = $this->cartItems->sum(function($item){
        return $item->total;
     });

     return  $sum;
  }

  public function cartItems()
  {
      return $this->hasMany(CartItem::class,'cart_id');
  }

}
