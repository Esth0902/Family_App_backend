<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MealPollController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\ShoppingListController;
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
//    Route::post('/menu/generate', [MenuController::class, 'generate']);
    Route::post('/recipes/suggest', [RecipeController::class, 'suggestIdeas']);
    Route::post('/recipes/ai-store', [RecipeController::class, 'storeFromAi']);
    Route::apiResource('recipes', RecipeController::class);
    Route::get('/recipes/{id}', [RecipeController::class, 'show']);
    Route::post('/meal-polls/{poll}/vote', [MealPollController::class, 'vote']);
    Route::apiResource('meal-polls', MealPollController::class);
    Route::post('/meal-polls/{poll}/validate', [MealPollController::class, 'validateResults']);
    Route::get('/shopping-list', [ShoppingListController::class, 'index']);
    Route::post('/shopping-list/{list}/items', [ShoppingListController::class, 'addItem']);
    Route::patch('/shopping-list/items/{item}', [ShoppingListController::class, 'updateItem']);
    Route::delete('/shopping-list/items/{item}', [ShoppingListController::class, 'removeItem']);
});
