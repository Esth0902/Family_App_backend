<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\HouseholdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/households', [HouseholdController::class, 'store']);
    Route::post('/households/{id}', [HouseholdController::class, 'createInvitation']);
    Route::post('/households/join', [HouseholdController::class, 'join']);
    Route::post('/menu/generate', [MenuController::class, 'generate']);
});
