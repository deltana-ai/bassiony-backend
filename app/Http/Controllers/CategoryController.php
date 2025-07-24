<?php
namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(CategoryRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            return CategoryResource::collection($this->crudRepository->all())->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Category $category): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item fetched successfully', new CategoryResource($category));
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

    public function update(CategoryRequest $request, Category $category): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->update($request->validated(), $category->id);

            $category = Category::find($category->id);
            $this->crudRepository->AddMediaCollection('image', $category);

            activity()->performedOn($category)->withProperties(['attributes' => $category])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY), null);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
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

    public function restore(Request $request) : \Illuminate\Http\JsonResponse
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
        $validator = Validator::make($request->all(), [
            'items'   => 'required|array',
            'items.*' => 'integer|exists:teams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }
        try {
            $items = $request->input('items');

            // Fetch the soft-deleted items
            $softDeletedItems = Category::withTrashed()
                ->whereIn('id', $items)
                ->whereNotNull('deleted_at')
                ->get();

            if ($softDeletedItems->isEmpty()) {
                return response()->json([
                    'message' => "One or more records do not exist or are not soft deleted. Please refresh the page.",
                ], 404);
            }

            // Force delete the soft-deleted items
            foreach ($softDeletedItems as $item) {
                $item->forceDelete();
            }

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function fetchCategory(Request $request)
    {
        try {
            $CategoryData = Category::get();
            return CategoryResource::collection($CategoryData)->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
