<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Interfaces\OrderRepositoryInterface;

class OrderController extends BaseController
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $validated = $request->validated();
            $order = $this->orderRepository->createOrder($validated, $request->user());
            return (new OrderResource($order))->additional([
                'result'  => 'Success',
                'message' => 'Order created successfully',
                'status'  => 200,
            ]);
        } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
        }
    }


    public function index(Request $request)
    {
        try {
            $orders = $request->user()
                ->orders()
                ->with('items.product')
                ->get();

            return OrderResource::collection($orders)->additional([
                'result'  => 'Success',
                'message' => 'Orders fetched successfully',
                'status'  => 200,
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Order $order)
    {
        try {
            return (new OrderResource($order->load('items.product')))->additional([
                'result'  => 'Success',
                'message' => 'Order details fetched successfully',
                'status'  => 200,
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
