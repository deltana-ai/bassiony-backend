<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Interfaces\FavoriteRepositoryInterface;
use App\Models\Favorite;
use Exception;
use Illuminate\Http\Request;

class FavoriteController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(FavoriteRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $favorites = FavoriteResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $favorites->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Favorite $favorite)
    {
        try {
            $favorite = new FavoriteResource($favorite);
            return $favorite->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(FavoriteRequest $request)
    {
        try {
            $existingFavorite = Favorite::where('user_id', $request->user_id)
                ->where('product_id', $request->product_id)
                ->where('pharmacist_id', $request->pharmacist_id)
                ->first();

            if ($existingFavorite) {
                return JsonResponse::respondError('هذه المفضلة موجودة بالفعل');
            }

            $favorite = $this->crudRepository->create($request->validated());
            return new FavoriteResource($favorite);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function update(FavoriteRequest $request, Favorite $favorite)
    {
        try {
            $this->crudRepository->update($request->validated(), $favorite->id);
            
            activity()->performedOn($favorite)->withProperties(['attributes' => $favorite])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->crudRepository->deleteRecords('favorites', $request['items']);
            return $count > 1
                ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED_MULTI_RESOURCE))
                : ($count == 222 ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED))
                    : JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY)));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Favorite::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Favorite::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function userFavorites($userId)
    {
        try {
            $favorites = Favorite::where('user_id', $userId)
                ->with(['product', 'pharmacist'])
                ->get();
                
            return FavoriteResource::collection($favorites)->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
      public function indexPublic(Request $request)
    {
        try {
        $query = Favorite::where('active', 1);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $favorites = $query->orderBy('position', 'asc')->get();
        return FavoriteResource::collection($favorites);
    } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}