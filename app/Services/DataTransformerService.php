<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DataTransformerService
{
    protected string $cacheKey;
    protected int $cacheDuration = 30;

    public function __construct()
    {
        $this->cacheKey = config('services.vercel.cache_key_transformed');
    }

    public function transformData(array $data): array
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () use ($data) {
            return $this->buildNestedStructure($data);
        });
    }

    private function buildNestedStructure(array $data): array
    {
        $nestedStructure = [];

        foreach ($data['items'] as $item) {
            $this->addUrlToStructure($nestedStructure, $item['fileUrl']);
        }

        return $nestedStructure;
    }

    private function addUrlToStructure(array &$structure, string $url): void
    {
        $parsedUrl = parse_url($url);
        $ipAddress = $parsedUrl['host'];
        $pathParts = $this->getPathParts($parsedUrl['path']);

        if (!isset($structure[$ipAddress])) {
            $structure[$ipAddress] = [];
        }

        $this->populateStructure($structure[$ipAddress], $pathParts, $url);
    }

    private function getPathParts(string $path): array
    {
        return explode('/', trim($path, '/'));
    }

    private function populateStructure(array &$currentLevel, array $pathParts, string $url): void
    {
        foreach($pathParts as $index => $part) {
            $isFile = $this->isFile($index, count($pathParts), $url, $part);
            if($isFile) {
                if (!is_array($currentLevel)) {
                    $currentLevel = [];
                }
                $currentLevel[] = $part;
            } else {
                if (!isset($currentLevel[$part])) {
                    $currentLevel[$part] = [];
                }
                $currentLevel = &$currentLevel[$part];
            }
        }
    }

    private function isFile(int $index, int $totalParts, string $url, string $part): bool
    {
        return $index === $totalParts - 1 && strpos($url, $part) === (strlen($url) - strlen($part));
    }
}
