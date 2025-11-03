<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Resources\WarehouseProductResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class WarehouseProductSearchController extends BaseController
{
    public function search(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user = Auth::guard('employees')->user();

            if (!$user) {
                return JsonResponse::respondError('يجب تسجيل الدخول كموظف في الشركة.', 401);
            }

            $company = $user->company ?? null;

            if (!$company) {
                return JsonResponse::respondError('هذا المستخدم لا ينتمي لأي شركة.', 403);
            }

            $warehouses = Warehouse::with(['products' => function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }])
            ->where('company_id', $company->id)
            ->get();

            // تصفية المخازن اللي فيها منتجات فعلاً
            $warehouses = $warehouses->filter(function ($warehouse) {
                return $warehouse->products->isNotEmpty();
            });

            if ($warehouses->isEmpty()) {
                return JsonResponse::respondError('لم يتم العثور على منتجات مطابقة في أي من مخازن الشركة.', 404);
            }

            return JsonResponse::respondSuccess(
                'تم العثور على المنتجات بنجاح.',
                WarehouseProductResource::collection($warehouses)
            );

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 500);
        }
    }
}
