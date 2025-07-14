<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\Cart;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CartResource;
use App\Interfaces\Client\CartRepositoryInterface;
use App\Repositories\Client\CartRepository;

class CartController extends BaseController
{

    protected mixed $crudRepository;

    public function __construct(CartRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $user = auth()->user();

        try {
            $cart = $this->crudRepository->all(
                with: ['items.pharmacyProduct.pharmacy', 'items.pharmacyProduct.offer'],
                conditions: ['user_id' => $user->id]
            )->first();

            if (!$cart || $cart->items->isEmpty()) {
                return JsonResponse::respondError('Cart is empty');
            }

            return (new CartResource($cart))->additional(JsonResponse::success());

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
     {
         $user = auth()->user();

         $validator = Validator::make($request->all(), [
             'product_id' => 'required|exists:pharmacy_products,id',
             'quantity' => 'required|integer|min:1',
         ]);

         if ($validator->fails()) {
             return JsonResponse::respondError($validator->errors()->first());
         }

         try {
             $cart = $user->cart()->firstOrCreate([
                 'user_id' => $user->id
             ]);

             $existingItem = $cart->items()->where('product_id', $request->product_id)->first();

             if ($existingItem) {
                 $existingItem->quantity += $request->quantity;
                 $existingItem->save();
             } else {
                 $cart->items()->create([
                     'product_id' => $request->product_id,
                     'quantity' => $request->quantity,
                 ]);
             }

             return JsonResponse::respondSuccess('Cart Item Added Successfully');

         } catch (Exception $e) {
             return JsonResponse::respondError($e->getMessage());
         }
     }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, string $id)
     {
         $user = auth()->user();

         $validator = Validator::make($request->all(), [
             'quantity' => 'required|integer|min:1',
         ]);

         if ($validator->fails()) {
             return JsonResponse::respondError($validator->errors()->first());
         }

         try {
             $cart = $user->cart()->with('items')->first();

             if (!$cart) {
                 return JsonResponse::respondError("Cart Doesn't Exist .");
             }

             $item = $cart->items()->where('id', $id)->first();

             if (!$item) {
                 return JsonResponse::respondError("Item Doesn't in Cart");
             }

             $item->update([
                 'quantity' => $request->quantity,
             ]);

             return JsonResponse::respondSuccess('Quantity Updated Successfully');

         } catch (Exception $e) {
             return JsonResponse::respondError($e->getMessage());
         }
     }


    /**
     * Remove the specified resource from storage.
     */
     public function destroy(string $id)
     {
         $user = auth()->user();

         try {
             $cart = $user->cart()->with('items')->first();

             if (!$cart) {
               return JsonResponse::respondError("Cart Doesn't Exist .");
             }

             $item = $cart->items()->where('id', $id)->first();

             if (!$item) {
               return JsonResponse::respondError("Item Doesn't in Cart");
             }

             $item->delete();

             return JsonResponse::respondSuccess('Item Deleted Successfully');

         } catch (Exception $e) {
             return JsonResponse::respondError($e->getMessage());
         }
     }

}
