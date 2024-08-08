<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/files-and-directories', [ApiController::class, 'getFilesAndDirectories']);
Route::get('/directories', [DirectoryController::class, 'getDirectories']);
Route::get('/files', [FileController::class, 'getFiles']);
