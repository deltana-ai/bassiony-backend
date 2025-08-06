<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
  protected $table = 'cart_items';
  protected $guarded = ['id'];

  public function pharmacyProduct()
  {
     return $this->BelongsTo(pharmacyProduct::class,'pharmacy_product_id');

  }


   public function getBasePriceAttribute()
   {
       return $this->pharmacyProduct->price;
   }

  public function getPriceAfterDiscountAttribute()
  {
    $price = $this->base_price;
    $offer = $this->pharmacyProduct->offer;

    if ($offer) {

            if ($offer->discount_type === 'percentage') {
                $price -= ($price * ($offer->value / 100));
            } elseif ($offer->discount_type === 'fixed') {
                $price -= $offer->value;
            }

    }

    return max($price, 0);
  }

   public function getFinalPriceAttribute()
   {
     return $this->price_after_discount + $this->tax_amount;

   }

   public function getTaxAmountAttribute()
   {
       $rate = $this->pharmacyProduct->tax_rate;
       return $this->price_after_discount * ($rate / 100);
   }

   public function getTotalTaxAttribute(){
     return $this->tax_amount * $this->quantity;
   }


    public function getTotalAttribute()
    {
        return $this->final_price  * $this->quantity;
    }



}
