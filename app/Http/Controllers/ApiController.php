<?php

namespace App\Http\Controllers;

use App\Services\ApiServiceInterface;
use App\Services\DataStorageService;
use App\Services\DataTransformerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    private string $cacheKey;

    public function __construct(
        protected ApiServiceInterface $apiService,
        protected DataTransformerService $dataTransformerService,
        protected DataStorageService $dataStorageService
    ) {
        $this->cacheKey = config('services.vercel.cache_key_transformed');
    }

    public function getFilesAndDirectories(): JsonResponse
    {
        $transformedData = Cache::get($this->cacheKey);

        if (!$transformedData) {
            $data = $this->apiService->fetchData();

            if (!$this->dataStorageService->isDataStored($data)) {
                $this->dataStorageService->storeData($data);
            }

            $transformedData = $this->dataTransformerService->transformData($data);
        }

        return response()->json($transformedData);
    }
}
