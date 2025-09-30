<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\BranchProductRequest;
use App\Http\Resources\BranchProductResource ;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchProduct;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class BranchProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(BranchRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index()
    {
        try {

            $branch_product_products = BranchProductResource::collection($this->crudRepository->all(
                [ 'products','pharmacy:id,name'],
                [],
                ['*']
            ));
            return $branch_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(Branch $branch,BranchProductRequest $request)
    {
            try {
                $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
                $branch_product = $branch->products()->attach($request->product_id,['branch_price' => $request->branch_price, 'stock' => $request->stock, 'reserved_stock' => $request->reserved_stock, 'expiry_date' => $expiry_date, 'batch_number' => $request->batch_number]);
                
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Branch $branch, int $productId): ?\Illuminate\Http\JsonResponse
    {
        try {
             $branch->load([
            'products' => function ($q) use ($productId) {
                $q->where('products.id', $productId);
            },
            'pharmacy:id,name'
        ]);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new BranchProductResource($branch));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(BranchProductRequest $request, Branch $branch)
    {
        try {
            $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                    ->format('Y-m-d');
            $branch->products()->syncWithoutDetaching($request->product_id,['branch_price' => $request->branch_price, 'stock' => $request->stock, 'reserved_stock' => $request->reserved_stock, 'expiry_date' => $expiry_date, 'batch_number' => $request->batch_number]);
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        }
        catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
  
    }


    public function destroy(Branch $branch,Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $branch->products()->detach($request->items);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }






}
