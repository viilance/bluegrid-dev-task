<?php

namespace Tests\Feature;

use App\Services\DataTransformerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DataTransformerServiceTest extends TestCase
{
    protected array $data;
    protected array $expectedTransformedData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'items' => [
                ['fileUrl' => 'http://34.8.32.234:48183/dir1/file1.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/dir2/file2.txt']
            ]
        ];

        $this->expectedTransformedData = [
            '34.8.32.234' => [
                'dir1' => ['file1.txt'],
                'dir2' => ['file2.txt']
            ]
        ];

        Config::set('services.vercel.cache_key_transformed', 'test_cache_key_transformed');
    }

    public function testTransformDataCachesAndReturnsTransformedData()
    {
        $data = $this->expectedTransformedData;

        Cache::shouldReceive('remember')
            ->once()
            ->with('test_cache_key_transformed', 3600, \Closure::class)
            ->andReturnUsing(function ($key, $duration, $callback) use ($data) {
                return $callback();
            });

        $dataTransformerService = new DataTransformerService();

        $transformedData = $dataTransformerService->transformData($this->data);

        $this->assertEquals($data, $transformedData);
    }

    public function testTransformDataReturnsCachedData()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('test_cache_key_transformed', 3600, \Closure::class)
            ->andReturn($this->expectedTransformedData);

        $dataTransformerService = new DataTransformerService();

        $transformedData = $dataTransformerService->transformData($this->data);

        $this->assertEquals($this->expectedTransformedData, $transformedData);
    }
}
