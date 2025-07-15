<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
  protected $table = 'order_items';
  protected $guarded = ['id'];

  public function product()
  {
      return $this->belongsTo(PharmacyProduct::class, 'product_id');
  }

  public function getTotalDiscountAttribute()
  {
      return bcmul($this->discount, $this->quantity, 2);
  }

}
