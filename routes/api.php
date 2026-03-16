<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/purchase', [TransactionController::class, 'purchase']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->only(['index','show']);

    Route::middleware('role:ADMIN,MANAGER,FINANCE')->group(function() {
        Route::apiResource('products', ProductController::class)->only(['store','update','destroy']);
    });

    Route::middleware('role:ADMIN,MANAGER')->group(function() {
        Route::apiResource('users', UserController::class);
        Route::post('/transactions/{transaction}/refund',[TransactionController::class, 'refund']);
    });

    Route::apiResource('clients', ClientController::class)->only(['index','show']);

    Route::apiResource('transactions', TransactionController::class)->only(['index','show']);

    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    });
});