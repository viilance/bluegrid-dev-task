<?php

namespace App\Services;

use App\Exceptions\ApiServiceException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService implements ApiServiceInterface
{
    protected string $cacheKey;
    protected string $url;
    protected int $cacheDuration = 30;

    public function __construct()
    {
        $this->cacheKey = config('services.vercel.cache_key_raw');
        $this->url = config('services.vercel.url');
    }

    public function fetchData()
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            $response = Http::get($this->url);

            if ($response->failed()) {
                throw new ApiServiceException('Failed to fetch data from API: ', 500);
            }

            $data = $response->json();
            Log::info('Data fetched at: ' . Carbon::now());

            return $data;
        });
    }
}
