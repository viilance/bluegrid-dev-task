<?php

namespace Tests\Feature;

use App\Exceptions\ApiServiceException;
use App\Services\ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchDataReturnsDataFromApi()
    {
        $apiResponse = [
            'items' => [
                ['fileUrl' => 'http://34.8.32.234:48183/dir1/file1.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/dir2/file2.txt']
            ]
        ];

        Http::fake([
            '*' => Http::response($apiResponse)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $duration, $callback) {
                return $callback();
            });

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Data fetched at: ');
            });

        $apiService = new ApiService();

        $data = $apiService->fetchData();
        $this->assertEquals($apiResponse, $data);
    }

    public function testFetchDataThrowsExceptionOnFailure()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $duration, $callback) {
                return $callback();
            });

        $apiService = new ApiService();

        $this->expectException(ApiServiceException::class);

        $apiService->fetchData();
    }
}
