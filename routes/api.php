<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProductController, BasketController, AuthController};


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);

    // Basket
    Route::get('/basket', [BasketController::class, 'show']);
    Route::post('/basket/items', [BasketController::class, 'addItem']);
    Route::delete('/basket/items/{item}', [BasketController::class, 'removeItem']);

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
});
