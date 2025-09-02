<?php

namespace App\Http\Controllers;

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

        return FavoriteResource::collection($favorites);
    }

    public function store(FavoriteRequest $request)
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'pharmacist_id' => $request->pharmacist_id,
        ]);

        return new FavoriteResource($favorite->load(['product', 'pharmacist']));
    }

    public function destroy(Favorite $favorite, Request $request)
    {
        if ($favorite->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        $favorite->delete();

        return response()->json(['message' => 'تم الحذف من المفضلة']);
    }
}
