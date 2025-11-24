<?php

namespace App\Repositories;

use App\Http\Requests\ResponseOfferRequest;
use App\Interfaces\ResponseOfferRepositoryInterface;
use App\Models\CompanyOffer;
use App\Models\ResponseOffer;
use App\Models\Role;
use App\Models\WarehouseProductBatch;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ResponseOfferRepository extends CrudRepository implements ResponseOfferRepositoryInterface
{
    protected Model $model;
 
    public function __construct(ResponseOffer $model)
    {
        $this->model = $model;
    }

    /**
     *  get warehouse offer orders
     */

    


    public function allForCompany($companyId = null)
    {
        $options = [];

        if (auth()->guard('pharmacists')->check()) {
            $options["pharmacy_id"] = auth()->guard('pharmacists')->user()->pharmacy_id;

            return $this->all(
                ['offer'],
                $options,
                ['*']
            );
        }

        if (auth()->guard('admins')->check()) {
            return $this->all(['offer']);
        }

        if ($companyId) {
            return $this->all(
                ['offer'],
                $options,
                ['*'],
                function ($query) use ($companyId) {
                    return $query->whereHas('offer', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    });
                }
            );
        }

        return $this->all(['offer'], $options, ['*']);
    }

    



    public function getBaseOffer($offerid)
    {
        return CompanyOffer::findOrFail($offerid);
    }


    

    public function updateResponse( array $data, ResponseOffer $responseOffer)
    {
       
        DB::transaction(function () use ($responseOffer, $data) {
            
            $product = $responseOffer->offer->product;
        
            $offer = $responseOffer->offer;

            switch ($data["status"]) {
                case 'approved':
                    $this->deductStockByBatch($product->id, $data["warehouse_id"], $responseOffer->quantity);
                    $offer->decrement('total_quantity', $responseOffer->quantity);
                    $offer->update(["warehouse_id"=>$data["warehouse_id"]]);
                    break;

            }

            $responseOffer->update(['status' => $data["status"]]);
        });

    }


    protected function deductStockByBatch($productId, $warehouseId,$quantity)
    {
            
            $totalStock = WarehouseProductBatch::where('product_id', $productId)->where('warehouse_id', $warehouseId)->sum('stock');
             
            $reservedStock = DB::table('warehouse_product')->where('product_id', $productId)->where('warehouse_id', $warehouseId)->value('reserved_stock') ?? 0;
           
            $availableStock = $totalStock - $reservedStock;
            
            if ($availableStock < $quantity) {
                throw new Exception("الكمية المطلوبة غير متوفرة", 1);
            }
           
            $batches = WarehouseProductBatch::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)->where('stock','>',0)
                ->orderBy('expiry_date','asc')->get();
            
            $remaining = $quantity ;

            foreach ($batches as  $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $available = $batch->stock;
                if ($available  >= $remaining ) {
                    $batch->decrement('stock', $remaining);
                    $remaining = 0;
                }
                else {
                    $batch->update(['stock'=> 0]);
                    $remaining -= $available ;
                }
            }
            if ($remaining > 0) {
                throw new Exception("الكمية المطلوبة غير متوفرة", 1);
                ;
            }
        
       
    }

   
   
}

