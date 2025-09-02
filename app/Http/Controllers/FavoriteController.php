<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // عرض المفضلة
    public function index(Request $request)
    {
        $favorites = Favorite::with(['product', 'pharmacist'])
            ->where('user_id', $request->user()->id)
            ->get();

        return JsonResponse::respondSuccess(
            JsonResponse::MSG_SUCCESS,
            FavoriteResource::collection($favorites)
        );
    }

    // إضافة للمفضلة
    public function store(FavoriteRequest $request)
    {
        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->where('pharmacist_id', $request->pharmacist_id)
            ->exists();

        if ($exists) {
            return JsonResponse::respondError(
                'هذا المنتج موجود بالفعل في المفضلة',
                422
            );
        }

        $favorite = Favorite::create([
            'user_id'       => $request->user()->id,
            'product_id'    => $request->product_id,
            'pharmacist_id' => $request->pharmacist_id,
        ]);

        return JsonResponse::respondSuccess(
            JsonResponse::MSG_ADDED_SUCCESSFULLY,
            new FavoriteResource($favorite->load(['product', 'pharmacist'])),
            201
        );
    }

    // حذف من المفضلة
    public function destroy(Favorite $favorite, Request $request)
    {
        if ($favorite->user_id !== $request->user()->id) {
            return JsonResponse::respondError(
                'غير مصرح لك',
                403
            );
        }

        $favorite->delete();

        return JsonResponse::respondSuccess(
            JsonResponse::MSG_DELETED_SUCCESSFULLY,
            null,
            200
        );
    }
}
