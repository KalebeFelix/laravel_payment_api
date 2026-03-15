<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::middleware('role:ADMIN,MANAGER,FINANCE')->group(function() {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    Route::middleware('role:ADMIN,MANAGER')->group(function() {
        Route::apiResource('users', UserController::class);
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    });
});