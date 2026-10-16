<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ExternalCatalogController;
use App\Http\Controllers\Api\MediaItemController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class,'login']);
Route::get('items', [MediaItemController::class, 'index']);
Route::get('items/{item}', [MediaItemController::class, 'show']);
Route::get('items/{item}/reviews', [ReviewController::class, 'index']);
Route::get('users/{user}/reviews', [ReviewController::class, 'byUser']);
Route::get('external/search', [ExternalCatalogController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('items', [MediaItemController::class, 'store']);
    Route::put('items/{item}', [MediaItemController::class, 'update']);
    Route::patch('items/{item}', [MediaItemController::class, 'update']);
    Route::delete('items/{item}', [MediaItemController::class, 'destroy']);
    Route::post('items/{item}/reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::patch('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
    Route::post('external/import', [ExternalCatalogController::class, 'import']);
});