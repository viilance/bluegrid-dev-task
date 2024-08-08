<?php

namespace App\Http\Controllers;

use App\Services\ApiServiceInterface;
use App\Services\DataStorageService;
use App\Services\DataTransformerService;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    public function __construct(
        protected ApiServiceInterface $apiService,
        protected DataTransformerService $dataTransformerService,
        protected DataStorageService $dataStorageService
    ) {
    }

    public function getFilesAndDirectories(): JsonResponse
    {
        $data = $this->apiService->fetchData();
        $this->dataStorageService->storeData($data);
        $transformedData = $this->dataTransformerService->transformData($data);

        return response()->json($transformedData);
    }
}
