<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Resources\ProductResource;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    use HttpResponses;

    public function index()
    {
        try {
            $user = auth()->user();
            $favorites = $user->favoriteProducts()->with('media')->get();

            return ProductResource::collection($favorites)
                ->additional(JsonResponse::success());
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

             $user = auth()->user();

            if ($user->favoriteProducts()->where('product_id', $request->product_id)->exists()) {
                return $this->respondError('المنتج بالفعل في المفضلة');
            }

            $user->favoriteProducts()->attach($request->product_id);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
