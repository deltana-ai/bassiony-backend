<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Resources\BranchProductResource;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class ProductBranchController extends BaseController
{
    public function searchProductInBranches(Request $request)
    {
        try {
            $user = Auth::guard('pharmacies')->user(); 
            if (!$user) {
                return JsonResponse::respondError('يجب تسجيل الدخول كصيدلي أولاً.', 401);
            }

            $productName = $request->query('name');

            if (!$productName) {
                return JsonResponse::respondError('يرجى إدخال اسم المنتج للبحث.', 400);
            }

            $product = Product::where('name', 'like', '%' . $productName . '%')->first();

            if (!$product) {
                return JsonResponse::respondError('لا يوجد منتج بهذا الاسم.', 404);
            }

            $branches = Branch::whereHas('products', function ($q) use ($product) {
                $q->where('products.id', $product->id);
            })
                ->with([
                    'products' => function ($q) use ($product) {
                        $q->where('products.id', $product->id);
                    },
                    'pharmacy:id,name'
                ])
                ->get();

            if ($branches->isEmpty()) {
                return JsonResponse::respondError('هذا المنتج غير متوفر في أي فرع.', 404);
            }

            return BranchProductResource::collection($branches)
                ->additional(JsonResponse::success('تم جلب بيانات الفروع التي تحتوي على المنتج بنجاح.'));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 500);
        }
    }
}
