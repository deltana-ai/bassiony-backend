<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { 
        $now = now();
        $expiryDate = $this->expiry_date ? \Carbon\Carbon::parse($this->expiry_date) : null;
        $isExpired = $expiryDate && $expiryDate < $now;
        
        $daysUntilExpiry = $expiryDate ? $now->diffInDays($expiryDate, false) : null;
        $status = 'good';
        if ($isExpired) {
            $status = 'expired';
        } elseif ($daysUntilExpiry !== null && $daysUntilExpiry <= 90) {
            $status = 'expiring_soon';
        }

       $data = [

            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'stock' => (int) $this->stock,
            'expiry_date' => $this->expiry_date,
            'days_until_expiry' => (int)$daysUntilExpiry,
            'status_label' => $this->getBatchStatusLabel($status, $daysUntilExpiry, $isExpired),
          
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product'=>
            [
                'id' => $this->product_id,
                'name' => $this->product?->name,
                'bar_code' => $this->product?->bar_code,
            ],
            
        ];
        if($this->warehouse){
            $data['warehouse'] = [
                'id' => $this->warehouse_id,
                'name' => $this->warehouse?->name,
            ];
        }
        if($this->branch)
        $data['branch'] =
            [
                'id' => $this->branch_id,
                'name' => $this->branch?->name,
            ];

        return $data;

    }



    protected function getBatchStatusLabel(string $status, ?int $daysUntilExpiry, bool $isExpired): string
    {
        if ($isExpired) {
            return 'منتهي الصلاحية';
        }

        if ($status === 'expiring_soon' && $daysUntilExpiry !== null) {
            return " تنتهي صلاحيته في" . (int)$daysUntilExpiry."  يوم"  ;
        }

        if ($daysUntilExpiry !== null) {
            return " متاح حتى ".(int)$daysUntilExpiry ." يوم ";
        }

        return 'غير منتهي الصلاحية';
    }
}
