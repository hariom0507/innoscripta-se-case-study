<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CategoryFilterRequest;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepository $categories) {}

    public function index(CategoryFilterRequest $request)
    {
        try {
            $categories = $this->categories->getFiltered($request->only(['search','page','per_page']));
            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No categories found',
                    'data' => []
                ]);
            }
            return response()->json(['success'=>true, 'data'=>$categories]);
        } catch (\Throwable $e) {
            Log::error('Category fetch error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to fetch categories.'],500);
        }
    }
}
