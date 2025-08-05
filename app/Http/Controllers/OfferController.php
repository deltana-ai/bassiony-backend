<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Resources\OfferProductResource;
use App\Models\Offer;
use App\Models\PharmacyProduct;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOfferRequest;





class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
public function createOffer(StoreOfferRequest $request)
{
    try {
        $auth = auth('pharmacist')->user(); // استخدم guard المناسب

        if (!$auth) {
            return response()->json([
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        }

        $data = $request->validated();

        $offer = Offer::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'discount_percentage' => $data['discount_percentage'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        // ربط المنتجات بالعرض
        $offer->products()->attach($data['products']);

        return response()->json([
            'message' => 'Offer created successfully',
            'data' => $offer->load('products'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        //
    }


    public function getOfferProducts(Request $request)
    {
        try {
            $offerProducts = PharmacyProduct::whereHas('offer')
                ->with(['offer', 'product'])
                ->get();

            return OfferProductResource::collection($offerProducts)
                ->additional(JsonResponse::success());
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
