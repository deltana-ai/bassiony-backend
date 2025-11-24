<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\Dashboard\CategoryResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(CategoryRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        // $this->middleware('permission:category-list|manage-site|manage-pharmacy|manage-company', ['only' => ['index','show']]);
        // $this->middleware('permission:category-create|manage-site', ['only' => [ 'store']]);
        // $this->middleware('permission:category-edit|manage-site', ['only' => [ 'update']]);
        // $this->middleware('permission:category-delete|manage-site', ['only' => ['destroy','restore','forceDelete']]);

    }

    public function index()
    {

        try {

            $categories = CategoryResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $categories->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(CategoryRequest $request)
    {
            try {
                $category = $this->crudRepository->create($request->validated());
                if (request('image') !== null) {
                    $this->crudRepository->AddMediaCollection('image', $category);
                }
                return new CategoryResource($category);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Category $category): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item Fetched Successfully', new CategoryResource($category));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(CategoryRequest $request, Category $category)
    {
        $this->crudRepository->update($request->validated(), $category->id);

        $categoryImage = $category;
        if (request('image') !== null) {
            $categoryImage = Category::find($category->id);
            $image = $this->crudRepository->AddMediaCollection('image', $categoryImage);
        }
        activity()->performedOn($category)->withProperties(['attributes' => $categoryImage])->log('update');
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('categories', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Category::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Category::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





}
