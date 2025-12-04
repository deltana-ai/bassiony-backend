<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyOrder extends Model
{
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::created(function($order){
            $order->update(['code' => 'ORD-' . $order->id]);
        });
    }

    public static $statusFlow = [
        'pending' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ["completed"],
        'completed' => [],
        'rejected' => [],
        'cancelled' => []
    ];

    public function canChangeStatusTo($newstatus)
    {
        $currentStatus = $this->status;
        if ($currentStatus == $newstatus) {
            return false;
        }
        if (!isset(self::$statusFlow[$currentStatus])) {
            return true;
        }
        return in_array($newstatus, self::$statusFlow[$currentStatus]);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

   

    public function items()
    {
        return $this->hasMany(PharmacyOrderItem::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

}
