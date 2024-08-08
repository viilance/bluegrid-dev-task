<?php

namespace App\Services;

use App\Models\Directory;
use App\Models\File;
use Illuminate\Support\Facades\DB;

class DataStorageService
{
    public function storeData(array $data): void
    {
        DB::transaction(function () use ($data) {
            $existingDirectories = Directory::pluck('id', 'name')->toArray();
            $directoryCache = [];
            $fileInserts = [];

            foreach ($data['items'] as $item) {
                $this->processItem($item['fileUrl'], $existingDirectories, $directoryCache, $fileInserts);
            }

            if (!empty($fileInserts)) {
                File::insert($fileInserts);
            }
        });
    }

    private function processItem(string $url, array &$existingDirectories, array &$directoryCache, &$fileInserts): void
    {
        $segments = $this->getUrlSegments($url);

        if (empty($segments)) {
            return;
        }

        $parentId = $this->processDirectories($segments, $existingDirectories, $directoryCache);

        $fileName = end($segments);
        if ($this->isFile($url, $fileName)) {
            $this->queueFileInsert($fileInserts, $fileName, $parentId);
        } else {
            $this->createDirectoryIfNeeded($directoryCache, $fileName, $parentId);
        }
    }

    private function getUrlSegments(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH);
        return explode('/', trim($path, '/'));
    }

    private function processDirectories(array $segments, array &$existingDirectories, &$directoryCache): ?int
    {
        $parentId = null;

        for ($i = 0; $i < count($segments) - 1; $i++) {
            $directoryName = $segments[$i];
            $fullPath = $parentId ? $parentId . '/' . $directoryName : $directoryName;

            if (isset($directoryCache[$fullPath])) {
                $parentId = $directoryCache[$fullPath];
                continue;
            }

            $parentId = $this->getOrCreateDirectory($directoryName, $parentId, $existingDirectories);
            $directoryCache[$fullPath] = $parentId;
        }

        return $parentId;
    }

    private function getOrCreateDirectory(string $directoryName, ?int $parentId, array &$existingDirectories): int
    {
        if (isset($existingDirectories[$directoryName])) {
            $directory = Directory::where('name', $directoryName)
                ->where('parent_id', $parentId)
                ->first();
        } else {
            $directory = Directory::create([
                'name' => $directoryName,
                'parent_id' => $parentId
            ]);
            $existingDirectories[$directoryName] = $directory->id;
        }

        return $directory->id;
    }

    private function isFile(string $url, string $fileName): bool
    {
        return $fileName && strpos($url, $fileName) === (strlen($url) - strlen($fileName));
    }

    private function queueFileInsert(array &$fileInserts, string $fileName, ?int $parentId): void
    {
        if ($parentId) {
            $fileInserts[] = [
                'name' => $fileName,
                'directory_id' => $parentId
            ];
        }
    }

    private function createDirectoryIfNeeded(array &$directoryCache, string $directoryName, ?int $parentId): void
    {
        $fullPath = $parentId . '/' . $directoryName;
        if (!isset($directoryCache[$fullPath])) {
            $directory = Directory::create([
                'name' => $directoryName,
                'parent_id' => $parentId
            ]);
            $directoryCache[$fullPath] = $directory->id;
        }
    }
}
