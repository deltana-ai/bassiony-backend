<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Exception;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function index(Request $request)
    {
       try {
            $addresses = AddressResource::collection($request->user()->addresses);
            return $addresses->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(AddressRequest $request)
    {
        try {
            $address = $request->user()->addresses()->create($request->validated());
            return new AddressResource($address);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Address $address)
    {
        try {
            $this->authorizeAddress($address);
            return new AddressResource($address);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function update(AddressRequest $request, Address $address)
    {
        try {
            $this->authorizeAddress($address);
            $address->update($request->validated());
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function destroyMany(Request $request)
    {
       try {
        $ids = $request->input('items', []);
        if (empty($ids)) {
            return response()->json(['message' => 'No items provided'], 400);
        }
        $addresses = Address::whereIn('id', $ids)
            ->where('user_id', auth()->id())
            ->get();
        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'No addresses found'], 404);
        }
        Address::whereIn('id', $addresses->pluck('id'))->delete();
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    private function authorizeAddress(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
