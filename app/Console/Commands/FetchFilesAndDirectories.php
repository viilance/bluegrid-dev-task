<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use Illuminate\Console\Command;

class FetchFilesAndDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-files-and-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, store and transform data from the /files-and-directories endpoint';

    public function __construct(protected ApiController $apiController)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $response = $this->apiController->getFilesAndDirectories();

        if ($response->status() === 200) {
            $this->info('Data fetched and processed successfully.');
        } else {
            $this->error('Failed to fetch data.');
        }
    }
}
