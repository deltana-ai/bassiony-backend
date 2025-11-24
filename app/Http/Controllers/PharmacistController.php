<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\Auth\RegisterPharmacistRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\PharmacistRequest;
use App\Http\Resources\PharmacistResource;
use App\Http\Resources\ProductResource;
use App\Interfaces\PharmacistRepositoryInterface;
use App\Models\Company;
use App\Models\Pharmacist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PharmacistController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacistRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $pharmacist = PharmacistResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $pharmacist->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(PharmacistRequest $request)
    {
        try {
            $pharmacist = $this->crudRepository->create($request->validated());
            if (request('image') !== null) {
                $this->crudRepository->AddMediaCollection('image', $pharmacist);
            }
            return new PharmacistResource($pharmacist);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Pharmacist $pharmacist): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item fetched successfully', new PharmacistResource($pharmacist));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


   public function update(PharmacistRequest $request, Pharmacist $pharmacist)
    {
        try {
            $data = $request->validated();
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            $this->crudRepository->update($data, $pharmacist->id);

            if ($request->hasFile('image')) {
                $pharmacist = Pharmacist::find($pharmacist->id);
                $this->crudRepository->AddMediaCollection('image', $pharmacist);
            }
            activity()
                ->performedOn($pharmacist)
                ->withProperties(['attributes' => $pharmacist])
                ->log('update');

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('pharmacists', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Pharmacist::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Pharmacist::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    // public function register(RegisterPharmacistRequest $request)
    // {
    //     $validated = $request->validated();

    //     try {
    //         $pharmacist = $this->crudRepository->createPharmacist($validated);

    //         $token = $pharmacist->createToken('user-token')->plainTextToken;

    //         return response()->json([
    //             'status'  => true,
    //             'pharmacist'    => new PharmacistResource($pharmacist),
    //             'message' => 'User Registered Successfully',
    //             'token'   => $token
    //         ], 200);

    //     } catch (\Throwable $th) {
    //         return JsonResponse::respondError($th->getMessage());
    //     }
    // }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required',
                'password' => 'required',
            ]);
            $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $pharmacist = Pharmacist::where($fieldType, $request->login)->first();
            if (!$pharmacist || !Hash::check($request->password, $pharmacist->password)) {
                return JsonResponse::respondError('The provided credentials are incorrect.');
            }
            $token = $pharmacist->createToken('pharmacist_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'pharmacist' => new PharmacistResource($pharmacist),
                'message' => 'Pharmacist Logged In Successfully',
                'token' => $token
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $pharmacist = Pharmacist::where($fieldType, $request->login)->first();

            if (!$pharmacist) {
                return JsonResponse::respondError('Pharmacist not found.');
            }
            $pharmacist->update([
                'password' => Hash::make($request->password)
            ]);

            return JsonResponse::respondSuccess('Password updated successfully');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        $user = $request->user('pharmacists'); // مهم جداً تحدد الجارد
        if ($user) {
            $user->tokens()->delete();
        }

        return JsonResponse::respondSuccess('Successfully logged out');
    }


    public function indexAllProductInCompany()
    {
        $companies = Company::with(['warehouses.batches.product'])
            ->get()
            ->map(function ($company) {

                // نجمع كل الباتشات اللي في مخازن الشركة
                $products = $company->warehouses
                    ->flatMap(fn($warehouse) => $warehouse->batches)
                    ->groupBy('product_id')
                    ->map(function ($group) {

                        // ناخد المنتج
                        $product = $group->first()->product;

                        // نضيف total_stock كخاصية على المنتج
                        $product->total_stock = $group->sum('stock');

                        // نرجع المنتج عشان يروح للريسورس
                        return $product;
                    })
                    ->values();

                return [
                    'company_id'   => $company->id,
                    'company_name' => $company->name,
                    'products'     => ProductResource::collection($products),
                ];
            });

        return response()->json($companies);
    }



}
