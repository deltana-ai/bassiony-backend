<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = ['id'];

    public function items()
    {
       return  $this->hasMany(OrderItem::class);
    }
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
    'subtotal' => 'float',
    'order_discount' => 'float',
    'total' => 'float',
    'paid_amount' => 'float',
    'remaining_amount' => 'float',
    'paid_from_wallet' => 'float',
    'paid_by_card' => 'float',
    'is_paid' => 'boolean',
    'due_date' => 'date',
    ];
}
