<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function getFilesAndDirectories(): JsonResponse
    {
        $cacheKey = 'files_and_directories';
        $cacheDuration = 60; // Cache data for an hour

        $data = Cache::remember($cacheKey, $cacheDuration, function () {
            $response = Http::get('https://rest-test-eight.vercel.app/api/test');
            $data = $response->json();
            Log::info('Data fetched at: ' . Carbon::now());

            $this->storeData($data);

            return $data;
        });

        return response()->json($this->transformData($data));
    }

    private function storeData($data): void
    {
        foreach ($data['items'] as $item) {
            $url = $item['fileUrl'];
            $path = parse_url($url, PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));

            if (empty($segments)) {
                continue;
            }

            $directory = null;
            $parentId = null;

            for ($i = 0; $i < count($segments) - 1; $i++) {
                $directoryName = $segments[$i];
                $directory = Directory::firstOrCreate([
                    'name' => $directoryName,
                    'parent_id' => $parentId
                ]);

                $parentId = $directory->id;
            }

            $fileName = end($segments);
            if ($fileName && strpos($url, $fileName) === (strlen($url) - strlen($fileName))) {
                if ($parentId) {
                    File::firstOrCreate([
                        'name' => $fileName,
                        'directory_id' => $parentId
                    ]);
                }
            } else {
                if ($parentId) {
                    Directory::firstOrCreate([
                        'name' => $fileName,
                        'parent_id' => $parentId
                    ]);
                }
            }
        }
    }

    private function transformData($data): array
    {
        $nestedStructure = [];

        foreach ($data['items'] as $item) {
            $url = $item['fileUrl'];
            $parsedUrl = parse_url($url);
            $ipAddress = $parsedUrl['host'];
            $path = trim($parsedUrl['path'], '/');
            $pathParts = explode('/', $path);

            $currentLevel = &$nestedStructure[$ipAddress];

            foreach($pathParts as $index => $part) {
                if($index === count($pathParts) - 1 && strpos($url, $part) === (strlen($url) - strlen($part))) {
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

        return $nestedStructure;
    }
}
