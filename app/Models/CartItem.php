<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
  protected $table = 'cart_items';
  protected $guarded = ['id'];

  public function pharmacyProduct()
  {
     return $this->BelongsTo(pharmacyProduct::class,'product_id');

  }


  public function getTotalAttribute(){

      return  $this->pharmacyProduct->priceAfterOffer() * $this->quantity;
  }




}
