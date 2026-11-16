<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;

Route::post('/api/register', [BookApiController::class, 'apiRegister']);
Route::post('/api/login', [BookApiController::class, 'apiLogin']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/books', [BookApiController::class, 'index']);       
    Route::post('/api/books', [BookApiController::class, 'store']);     
    Route::delete('/api/books/{id}', [BookApiController::class, 'destroy']); 
});