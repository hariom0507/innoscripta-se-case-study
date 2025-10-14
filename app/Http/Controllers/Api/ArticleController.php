<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Repositories\ArticleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function __construct(protected ArticleRepository $articles) {}

    public function index(ArticleFilterRequest $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'source', 'category', 'date', 'page', 'per_page']);
            $articles = $this->articles->getFilteredArticles($filters);

            if ($articles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No articles found',
                    'data'    => []
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $articles
            ]);

        } catch (\Throwable $e) {
            Log::error('Article fetch error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles.'
            ], 500);
        }
    }
}
