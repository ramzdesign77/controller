<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\AuthController;

// Route Acara 25 (CRUD Buku)
Route::apiResource('books', BookController::class);

// Route Public Login
Route::post('/login', [AuthController::class, 'login']);

// Route Terproteksi Sanctum (Acara 27 & 28)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});