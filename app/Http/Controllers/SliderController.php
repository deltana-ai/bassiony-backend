<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\SliderRequest;
use App\Http\Resources\SliderResource;
use App\Interfaces\SliderRepositoryInterface;
use App\Models\Slider;
use Exception;
use Illuminate\Http\Request;

class SliderController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(SliderRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $slider = SliderResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $slider->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Slider $slider)
    {
        try {
            $slider = new SliderResource($slider);
            return $slider->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(SliderRequest $request)
    {
        try {
            $slider = $this->crudRepository->create($request->validated());
            if (request('image') !== null) {
                $this->crudRepository->AddMediaCollection('image', $slider);
            }
            return new SliderResource($slider);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(SliderRequest $request, Slider $slider)
    {
        $this->crudRepository->update($request->validated(), $slider->id);

        $sliderImage = $slider;
        if (request('image') !== null) {
            $sliderImage = Slider::find($slider->id);
            $image = $this->crudRepository->AddMediaCollection('image', $sliderImage);
        }
        activity()->performedOn($slider)->withProperties(['attributes' => $sliderImage])->log('update');
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->crudRepository->deleteRecords('sliders', $request['items']);
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
            $this->crudRepository->restoreItem(Slider::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Slider::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function indexPublic()
    {
        try {

            $slider = Slider::where('active', 1)
                ->orderBy('position', 'asc')
                ->get();


            return SliderResource::collection($slider);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
