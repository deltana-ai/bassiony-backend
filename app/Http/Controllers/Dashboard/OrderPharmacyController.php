<?php

namespace App\Http\Controllers;

use App\Http\Requests\PharmacyOrderRequest;
use App\Http\Resources\PharmacyOrderResource;
use App\Interfaces\PharmacyOrderRepositoryInterface;
use App\Models\Company;
use App\Models\PharmacyOrder;
use App\Models\Warehouse;
use App\Services\PharmacyOrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderPharmacyController extends Controller
{
    use AuthorizesRequests;

    protected PharmacyOrderService $orderService;
    
    protected mixed $crudRepository;

    public function __construct(PharmacyOrderService $orderService , PharmacyOrderRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->orderService = $orderService;

        // $this->middleware('permission:employee-list|manage-company', ['only' => ['index','show']]);
        // $this->middleware('permission:employee-create|manage-company', ['only' => [ 'store']]);
        // $this->middleware('permission:employee-edit|manage-company', ['only' => [ 'update','assignWarehouse','assignRole']]);
        // $this->middleware('permission:employee-delete|manage-company', ['only' => ['destroy','restore','forceDelete']]);

    }

    /**
     * Create order from cart
     */
    public function createFromCart(PharmacyOrderRequest $request): JsonResponse
    {
        

        $pharmacy_id = auth('pharmacists')->user()->pharmacy_id;

        try {
            $data  = $request->validated();

            $data['pharmacy_id'] = $pharmacy_id;

            $order = $this->orderService->createOrderFromCart($data);

            return JsonResponse::respondSuccess('تم انشاء الطلب بنجاح', new PharmacyOrderResource($order));


        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(),400);
        }
        
    }

    /**
     * Get order by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $with = [
                'items.product',
                'pharmacy',
                'warehouse',
                'branch',
                'company'
            ];
            $order = $this->crudRepository->find($id,$with);

            return JsonResponse::respondSuccess('تم جلب الطلب بنجاح', new PharmacyOrderResource($order));


        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage(),404);
        }
    }

    /**
     * Get all orders for a pharmacy
     */
    public function getPharmacyOrders( Request $request)
    {
        try {

            $pharmacyId = auth('pharmacists')->user()->pharmacy_id;
        
            $order = PharmacyOrderResource::collection($this->crudRepository->all(
                [ "company" ,"branch"],
                ["pharmacy_id" => $pharmacyId],
                ['*']
            ));
            return $order->additional(JsonResponse::success());

        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage(),404);
        }
        
    }

    /**
     * Get all orders for a company
     */
    public function getCompanyOrders( Request $request)
    {
        try {

            $companyId = auth('employees')->user()->company_id;
        
            $order = PharmacyOrderResource::collection($this->crudRepository->all(
                [ "pharmacy","warehouse","company"],
                ["company_id" => $companyId],
                ['*']
            ));
            return $order->additional(JsonResponse::success());

        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage(),404);
        }
    }

    /**
     * Get available warehouses for order fulfillment
     */
    public function getWarehouseOrders(Warehouse $warehouse )
    {
        try {
            
            $companyId = auth('employees')->user()->company_id;

            $this->authorize('manage', $warehouse);

            
            $order = PharmacyOrderResource::collection($this->crudRepository->all(
                [ "pharmacy","warehouse","company"],
                ["warehouse_id" => $warehouse->id],
                ['*']
            ));
            return $order->additional(JsonResponse::success());

        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage(),400);
        }
    }

    /**
     * Approve order and assign warehouse
     */
    public function approve(Request $request, PharmacyOrder $pharmacyOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return JsonResponse::respondError($validator->errors(),422);
        }

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $this->authorize('manage', $warehouse);

        try {
            if (!$pharmacyOrder->canChangeStatusTo('approved')) {
                return JsonResponse::respondError('لا يمكنك الموافقة على الطلب',422);
            }
            $order = $this->orderService->approveOrder($pharmacyOrder->id, $request->warehouse_id);

            return JsonResponse::respondSuccess('تم الموافقة على الطلب واضافة المخزن بنجاح', new PharmacyOrderResource($order));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Reject order
     */
    public function reject(Request $request, PharmacyOrder $pharmacyOrder)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        $companyId = auth('employees')->user()->company_id;

        if ($validator->fails()) {
            return JsonResponse::respondError($validator->errors(),422);

        }

        try {
            if (!$pharmacyOrder->canChangeStatusTo('rejected')) {
                return JsonResponse::respondError('لا يمكنك الرفض على الطلب',422);
            }
            $order = $this->orderService->rejectOrder($pharmacyOrder->id, $request->reason);
            
            return JsonResponse::respondSuccess('تم الرفض على الطلب بنجاح');

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Mark order as delivered
     */
    public function markAsShipped(PharmacyOrder $pharmacyOrder): JsonResponse
    {
        try {

            if (!$pharmacyOrder->canChangeStatusTo('shipped')) {
                return JsonResponse::respondError('لا يمكنك التسليم على الطلب',422);
            }

            $pharmacyOrder->update(['status' => 'shipped']);

            return JsonResponse::respondSuccess('تم التسليم على الطلب بنجاح');


        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Create return request
     */
    public function createReturn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_order_id' => 'required|exists:pharmacy_orders,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->orderService->createReturnRequest($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Return request created successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all return requests for a company
     */
    public function getCompanyReturns(int $companyId): JsonResponse
    {
        $returns = \DB::table('pharmacy_order_returns')
            ->join('pharmacy_orders', 'pharmacy_order_returns.pharmacy_order_id', '=', 'pharmacy_orders.id')
            ->join('pharmacies', 'pharmacy_order_returns.pharmacy_id', '=', 'pharmacies.id')
            ->where('pharmacy_orders.company_id', $companyId)
            ->select([
                'pharmacy_order_returns.*',
                'pharmacies.name as pharmacy_name',
                'pharmacy_orders.total_price'
            ])
            ->orderBy('pharmacy_order_returns.created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $returns
        ]);
    }

    /**
     * Approve return request
     */
    public function approveReturn(int $returnId): JsonResponse
    {
        try {
            \DB::table('pharmacy_order_returns')
                ->where('id', $returnId)
                ->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject return request
     */
    public function rejectReturn(int $returnId): JsonResponse
    {
        try {
            \DB::table('pharmacy_order_returns')
                ->where('id', $returnId)
                ->update([
                    'status' => 'rejected',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}