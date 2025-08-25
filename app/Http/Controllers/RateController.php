<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\RateRequest;
use App\Http\Resources\RetatResource; // الإسم كما هو
use App\Interfaces\RateRepositoryInterface;
use App\Models\Product;
use App\Models\ProductRating;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RateController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(RateRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    /**
     */
    public function index()
    {
        try {
            $ratings = RetatResource::collection($this->crudRepository->all([], [], ['*']));
            return $ratings->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function show(ProductRating $rating)
    {
        try {
            $rating = new RetatResource($rating);
            return $rating->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function store(RateRequest $request)
    {
        DB::beginTransaction();
        try {
            $existingRating = ProductRating::where('user_id', $request->user_id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingRating) {
                DB::rollBack();
                return JsonResponse::respondError('هذا التقييم موجود بالفعل');
            }

            /** @var ProductRating $rating */
            $rating = $this->crudRepository->create($request->validated());

            $this->applyAggregateOnCreate($rating->product_id, (float) $rating->rating);

            DB::commit();
            return (new RetatResource($rating))->additional(JsonResponse::success());
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function update(RateRequest $request, ProductRating $rating)
    {
        DB::beginTransaction();
        try {
            $oldRate = (float) $rating->rating;

            $this->crudRepository->update($request->validated(), $rating->id);
            $rating->refresh();

            $this->applyAggregateOnUpdate($rating->product_id, $oldRate, (float) $rating->rating);

            activity()->performedOn($rating)->withProperties(['attributes' => $rating])->log('update');

            DB::commit();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $ids = (array) ($request['items'] ?? []);
            if (empty($ids)) {
                DB::rollBack();
                return JsonResponse::respondError('لا توجد عناصر للحذف');
            }

            $productIds = ProductRating::whereIn('id', $ids)->pluck('product_id')->unique()->values();

            $count = $this->crudRepository->deleteRecords('product_ratings', $ids);

            foreach ($productIds as $pid) {
                $this->recalcProductAggregates((int) $pid);
            }

            DB::commit();

            return $count > 1
                ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED_MULTI_RESOURCE))
                : ($count == 222 ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED))
                    : JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY)));
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $ids = (array) ($request['items'] ?? []);
            $this->crudRepository->restoreItem(ProductRating::class, $ids);

            $productIds = ProductRating::whereIn('id', $ids)->pluck('product_id')->unique()->values();
            foreach ($productIds as $pid) {
                $this->recalcProductAggregates((int) $pid);
            }

            DB::commit();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $ids = (array) ($request['items'] ?? []);

            $productIds = ProductRating::withTrashed()->whereIn('id', $ids)->pluck('product_id')->unique()->values();

            $this->crudRepository->deleteRecordsFinial(ProductRating::class, $ids);

            foreach ($productIds as $pid) {
                $this->recalcProductAggregates((int) $pid);
            }

            DB::commit();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

  
    /**
     */
    public function indexPublic(Request $request)
    {
        try {
            $query = ProductRating::query()
                ->select('product_ratings.*')
                ->join('products', 'product_ratings.product_id', '=', 'products.id')
                ->where('products.active', 1);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('products.name', 'LIKE', "%{$search}%");
            }

            $ratings = $query->orderBy('products.position', 'asc')->get();
            return RetatResource::collection($ratings);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    private function applyAggregateOnCreate(int $productId, float $newRate): void
    {
        /** @var Product $product */
        $product = Product::lockForUpdate()->find($productId);
        if (!$product) return;

        $count = (int) $product->rating_count;
        $avg   = (float) $product->rating;

        $newCount = $count + 1;
        $newAvg = $newCount > 0 ? (($avg * $count) + $newRate) / $newCount : $newRate;

        $product->update([
            'rating_count' => $newCount,
            'rating'       => round($newAvg, 1),
        ]);
    }

    /**
     */
    private function applyAggregateOnUpdate(int $productId, float $oldRate, float $newRate): void
    {
        /** @var Product $product */
        $product = Product::lockForUpdate()->find($productId);
        if (!$product) return;

        $count = max(1, (int) $product->rating_count);
        $avg   = (float) $product->rating;

        $newAvg = (($avg * $count) - $oldRate + $newRate) / $count;

        $product->update([
            'rating' => round($newAvg, 1),
        ]);
    }


    private function recalcProductAggregates(int $productId): void
    {
        $agg = ProductRating::where('product_id', $productId)
            ->selectRaw('COUNT(*) as c, COALESCE(AVG(rating), 0) as a')
            ->first();

        /** @var Product $product */
        $product = Product::query()->find($productId);
        if (!$product) return;

        $product->update([
            'rating_count' => (int) ($agg->c ?? 0),
            'rating'       => round((float) ($agg->a ?? 0), 1),
        ]);
    }
}
