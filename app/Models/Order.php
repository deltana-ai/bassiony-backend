<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends BaseModel
{
    protected $table = 'orders';

    public function items()
    {
      $this->hasMany(OrderItem::class);
    }
  
}
