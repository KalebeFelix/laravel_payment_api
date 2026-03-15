<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return User::all();
});

Route::get('/login', [AuthController::class, 'login']);

Route::get('/ping', function () {
    return response()->json(["status" => "ok"]);
});
