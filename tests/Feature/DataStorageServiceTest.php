<?php

namespace Tests\Feature;

use App\Models\Directory;
use App\Models\File;
use App\Services\DataStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'items' => [
                ['fileUrl' => 'http://34.8.32.234:48183/dir1/file1.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/dir2/file2.txt']
            ]
        ];
    }

    public function testIsDataStoredReturnsTrueWhenDataIsStored()
    {
        $dir1 = Directory::create(['name' => 'dir1', 'parent_id' => null]);
        File::create(['name' => 'file1.txt', 'directory_id' => $dir1->id]);

        $dir2 = Directory::create(['name' => 'dir2', 'parent_id' => null]);
        File::create(['name' => 'file2.txt', 'directory_id' => $dir2->id]);

        $dataStorageService = new DataStorageService();

        $result = $dataStorageService->isDataStored($this->data);

        $this->assertTrue($result);
    }

    public function testIsDataStoredReturnsFalseWhenDataIsNotStored()
    {
        $dataStorageService = new DataStorageService();

        $result = $dataStorageService->isDataStored($this->data);

        $this->assertFalse($result);
    }

    public function testStoreDataStoresDataCorrectly()
    {
        $dataStorageService = new DataStorageService();

        $dataStorageService->storeData($this->data);

        $this->assertDatabaseHas('directories', ['name' => 'dir1']);
        $this->assertDatabaseHas('files', ['name' => 'file1.txt']);
        $this->assertDatabaseHas('directories', ['name' => 'dir2']);
        $this->assertDatabaseHas('files', ['name' => 'file2.txt']);
    }
}
