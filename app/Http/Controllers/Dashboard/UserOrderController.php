<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateOrderStatusRequest;

use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Interfaces\OrderRepositoryInterface;
use App\Helpers\JsonResponse;

class UserOrderController extends Controller
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request)
    {
        try {
            $condition =[];

            $pharmacyId = auth()->user()->pharmacy_id;
            $condition =["pharmacy_id" => $pharmacyId ];
            
            $orders = OrderResource::collection($this->orderRepository->all([],$condition,
                ['*']
            ));

            return $orders->additional(JsonResponse::success());

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function show(Order $order)
    {
        try {
            $order->load('items.product');
            return JsonResponse::respondSuccess('تم جلب الطلب بنجاح', new OrderResource($order));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(UpdateOrderStatusRequest $request, Order $order)
    {
        try {

            $pharmacyId = auth()->user()->pharmacy_id;
            
            if ($pharmacyId !== $order->pharmacy_id) {
                abort(403);
            }

            if (in_array($order->status, ['rejected', 'delivered'])) {
                return JsonResponse::respondError(
                    'غير الممكن تغير حالة الطلب بعد ما تم ارساله او رفضه.'
                );
            }

            if ($order->status === 'approved' && $request->status === 'rejected') {
                return JsonResponse::respondError(
                    'غير الممكن تغيير حالة الطلب للرفض بعد الموافقة.'
                );
            }

            $order->status = $request->status;
            $order->save();
            return JsonResponse::respondSuccess('تم تحديث حالة الطلب بنجاح', new OrderResource($order));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }



     public function destroy(Request $request ,Order $order): ?\Illuminate\Http\JsonResponse
    {
        try {
            $pharmacyId = auth()->user()->pharmacy_id;

            if ($pharmacyId !== $order->pharmacy_id) {
                abort(403);
            }
            if (!in_array($order->status, ['rejected', 'delivered'])) {
                    return JsonResponse::respondError(
                        'غير الممكن حذف الطلب الا اذا تم ارساله او رفضه.'
                    );
                }
        
            $order ->delete();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




}
