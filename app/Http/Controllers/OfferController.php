<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\OfferRequest;
use App\Http\Resources\OfferResource;
use App\Interfaces\OfferRepositoryInterface;
use App\Models\Offer;
use Exception;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    protected $offerRepository;

    public function __construct(OfferRepositoryInterface $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    public function index()
    {
        try {
            $offers = $this->offerRepository->all([], ['products', 'pharmacy'], ['*']);
            return OfferResource::collection($offers)->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Offer $offer)
    {
        try {
            $offer->load(['products', 'pharmacy']);
            return (new OfferResource($offer))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(OfferRequest $request)
    {
        try {
            $offer = $this->offerRepository->create($request->validated());
            
            if ($request->has('products')) {
                $offer->products()->attach($request->products);
            }
            
            $offer->load(['products', 'pharmacy']);
            return (new OfferResource($offer))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function update(OfferRequest $request, Offer $offer)
    {
        try {
            $this->offerRepository->update($request->validated(), $offer->id);
            
            if ($request->has('products')) {
                $offer->products()->sync($request->products);
            }
            
            $offer->load(['products', 'pharmacy']);
            return (new OfferResource($offer))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $count = $this->offerRepository->deleteRecords('offers', $request->items);
            
            if ($count > 1) {
                return JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED_MULTI_RESOURCE));
            }
            
            if ($count == 222) {
                return JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED));
            }
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        try {
            $this->offerRepository->restoreItem(Offer::class, $request->items);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function forceDelete(Request $request)
    {
        try {
            $this->offerRepository->deleteRecordsFinial(Offer::class, $request->items);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function indexPublic(Request $request)
    {
        try {
            $query = Offer::with(['products', 'pharmacy'])
                        ->where('is_active', true)
                        ->where(function($q) {
                            $q->whereNull('start_date')
                              ->orWhereDate('start_date', '<=', now());
                        })
                        ->where(function($q) {
                            $q->whereNull('end_date')
                              ->orWhereDate('end_date', '>=', now());
                        });

            if ($request->has('search') && !empty($request->search)) {
                $query->whereHas('products', function($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $offers = $query->orderBy('created_at', 'desc')->get();
            return OfferResource::collection($offers);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function addProductToOffer(Request $request, Offer $offer)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $offer->products()->attach($request->product_id);
            $offer->load('products');
            
            return (new OfferResource($offer))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function removeProductFromOffer(Request $request, Offer $offer)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $offer->products()->detach($request->product_id);
            $offer->load('products');
            
            return (new OfferResource($offer))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}