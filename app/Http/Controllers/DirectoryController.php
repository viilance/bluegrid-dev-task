<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use Illuminate\Http\JsonResponse;

class DirectoryController extends Controller
{
    public function getDirectories(): JsonResponse
    {
        $files = Directory::paginate(100);
        return response()->json($files);
    }
}
