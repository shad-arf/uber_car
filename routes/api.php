<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;

use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login'); // ✅ Ensure this exists
Route::get('/items', [ItemController::class, 'index']); // Get all items
Route::get('/items/{id}', [ItemController::class, 'show']); // Get a specific item
Route::post('/items', [ItemController::class, 'store']); // Create an item

// Protected Routes (Require JWT Authentication)
Route::middleware('auth:api')->group(function () {
    Route::get('/users', action: [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::patch('/items/{id}', [ItemController::class, 'update']); // Patch update an item
    Route::delete('/items/{id}', [ItemController::class, 'destroy']); // Delete an item
    // optional route to mark item as taken
    Route::post('items/{id}/take', [ItemController::class, 'takeItem']);
});
