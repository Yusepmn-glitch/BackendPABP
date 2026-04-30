<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KostController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomPhotoController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FacilityController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    // Kosts
    Route::apiResource('kosts', KostController::class);
    Route::get('/my-kosts', [KostController::class, 'myKosts']);
    // Rooms
    Route::apiResource('kosts.rooms', RoomController::class);
    // Room Photos
    Route::post('/rooms/{room}/photos', [RoomPhotoController::class, 'store']);
    Route::delete('/room-photos/{photo}', [RoomPhotoController::class, 'destroy']);
    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{kost}', [FavoriteController::class, 'destroy']);
    // Contacts
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::put('/contacts/{contact}/status', [ContactController::class, 'updateStatus']);
    // Search
    Route::get('/search/nearby', [SearchController::class, 'nearby']);
    Route::get('/search/filter', [SearchController::class, 'filter']);
    // Facilities
    Route::get('/facilities', [FacilityController::class, 'index']);
});
