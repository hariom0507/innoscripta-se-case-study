<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SourceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SourceFilterRequest;

class SourceController extends Controller
{
    public function __construct(protected SourceRepository $sources) {}

    public function index(SourceFilterRequest $request)
    {
        try {
            $sources = $this->sources->getFiltered($request->only(['search','page','per_page']));
            if ($sources->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No sources found',
                    'data'    => []
                ]);
            }
            return response()->json(['success'=>true, 'data'=>$sources]);
        } catch (\Throwable $e) {
            Log::error('Source fetch error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Failed to fetch sources.'],500);
        }
    }
}
