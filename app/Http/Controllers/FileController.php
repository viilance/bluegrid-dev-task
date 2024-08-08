<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    public function getFiles(): JsonResponse
    {
        $files = File::paginate(100);
        return response()->json($files);
    }
}
