<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\DeleteBatchRequest;
use App\Http\Requests\{ReservedStockRequest,UpdateBatchStockRequest };
use App\Http\Requests\BranchProductRequest;
use App\Http\Resources\BatchResource;
use App\Http\Resources\BranchProduct2Resource;
use App\Http\Resources\BranchProductResource ;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\BranchProductBatch;
use App\Imports\BranchProductBatchImport;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\FileImportRequest;
class BranchProductController extends BaseController
{

    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(BranchRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index($id)
    {
        try {
        
            $branch_product_products = BranchProduct2Resource::collection($this->crudRepository->getBranchProducts( $id));
            return $branch_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    
    public function addBatch(Branch $branch,BranchProductRequest $request){
        try {
            $this->authorize('manage', $branch);
            $data = $this->handleData( $request, $branch->id);
            $check_data = $data;
            unset($check_data["stock"]);

            DB::transaction(function () use ($check_data, $data, $branch, $request) {
                $batch = BranchProductBatch::where($check_data)->first();

                if ($batch) {
                    $batch->increment('stock', $data['stock']);
                } else {
                    if (!$branch->products()->where('product_id', $request->product_id)->exists()) {
                        $branch->products()->attach($request->product_id, [
                            'reserved_stock' => 0,
                        ]);
                    }

                    BranchProductBatch::create($data);
                }
            });
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function addReservedStock(Branch $branch ,ReservedStockRequest $request)
    {
       try {
            $this->authorize('manage', $branch);
            if($branch->products()->where("product_id" ,$request->product_id)->exists())
            {
                $branch->products()->updateExistingPivot($request->product_id, [
                    'reserved_stock' => $request->reserved_stock,
                ]);
            }
            else {
                $branch->products()->attach($request->product_id, [
                    'reserved_stock' => $request->reserved_stock,
                ]);
            
            }
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Branch $branch, Request $request, int $productId)
    {
        try {
            
            $batches = $this->crudRepository->getProductBatches( $productId, $branch->id);
            return JsonResponse::respondSuccess('Item Fetched Successfully', BatchResource::collection($batches));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateBatchStock(Branch $branch, UpdateBatchStockRequest $request)
    {
       try {
            $this->authorize('manage', $branch);
            
             BranchProductBatch::where("product_id" ,$request->product_id)->where("branch_id" ,$branch->id )->where('batch_number' ,$request->batch_number )
               ->update(["stock"=>$request->stock]);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////




    public function destroy(Branch $branch,DeleteBatchRequest $request): ?\Illuminate\Http\JsonResponse
    {
        try {

            $this->authorize('manage', $branch);
            $batch = BranchProductBatch::where("batch_number",$request->batch_number)
            ->where("product_id",$request->product_id)
            ->where("branch_id",$branch->id)
            ->first();
            if ($batch ) {
                $batch->delete();
            }
            else{
                return JsonResponse::respondError("هذا الباتش غير متاح ");
            }
            

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 public function import(FileImportRequest $request, Branch $branch)
    {
        try {
            $import = new BranchProductBatchImport($branch);
            Excel::import($import, $request->file('file'));
            $errors = $import->getErrors();

            if (!empty($errors)) {
                return JsonResponse::respondError($errors);
            }
            if ($import->failures()->isNotEmpty()) {
                return JsonResponse::respondError($import->failures());
            }

            return JsonResponse::respondSuccess( ' تم استيراد البيانات بنجاح');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
        
    }
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function handleData(BranchProductRequest $request,int $branch_id)
    {
        $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
        $data['branch_id'] = $branch_id;
        $data['product_id'] = $request->product_id;
        $data['stock'] = $request->stock;
        $data['expiry_date'] = $expiry_date;
        $data['batch_number'] = $request->batch_number;
        return $data;
    }






}
