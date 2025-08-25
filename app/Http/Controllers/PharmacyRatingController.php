<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\PharmacyRatingRequest;
use App\Http\Resources\PharmacyRatingResource; // الإسم كما هو
use App\Interfaces\PharmacyRateRepositoryInterface;
use App\Models\Pharmacy;
use App\Models\PharmacyRating;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyRatingController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacyRateRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    /**
     */
    public function index()
    {
        try {
            $ratings = PharmacyRatingResource::collection($this->crudRepository->all([], [], ['*']));
            return $ratings->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function show( $id)
    {
        try {
             $rating =  $this->crudRepository->find($id);
            return (new PharmacyRatingResource($rating))->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function store(PharmacyRatingRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $existingRating = PharmacyRating::where('user_id', $user->id)
                ->where('pharmacy_id', $request->pharmacy_id)
                ->first();

            if ($existingRating) {
                DB::rollBack();
                return JsonResponse::respondError('هذا التقييم موجود بالفعل');
            }
            $data = $request->validated();
            $data['user_id'] = $user->id;

            /** @var ProductRating $rating */
            $rating = $this->crudRepository->create($data);

            activity()->performedOn($rating)->withProperties(['attributes' => $rating])->log('create');
            DB::commit();
            return (new PharmacyRatingResource($rating))->additional(JsonResponse::success());
        } catch (Exception $e) {
            DB::rollBack();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     */
    public function update(PharmacyRatingRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $rating = $this->crudRepository->find($id);
            $this->crudRepository->update($request->validated(), $rating->id);
    
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
      try {
            $this->crudRepository->deleteRecords('pharmacy_ratings', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
         
    }

    /**
     */

    /**
     */
   

   

    /**
     */
    public function indexPublic(Request $request,$id)
    {
        try {
            $query = PharmacyRating::query()->where('pharmacy_id',$id)
                ->select('pharmacy_ratings.*')
                ->join('pharmacies', 'pharmacy_ratings.pharmacy_id', '=', 'pharmacies.id');

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('pharmacies.name', 'LIKE', "%{$search}%");
            }
            $perPage = $request->get('per_page', 10);


            $ratings = $query->orderBy('pharmacy_ratings.rating', 'desc')->paginate($perPage);
            return PharmacyRatingResource::collection($ratings);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

 
   

    /**
     */
    
}
