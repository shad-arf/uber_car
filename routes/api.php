<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\FeedBackController;
use App\Http\Controllers\Api\ItemController;

use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login'); // ✅ Ensure this exists
Route::get('/items', [ItemController::class, 'index']); // Get all items

Route::apiResource('feedbacks', controller: FeedbackController::class);
// Protected Routes (Require JWT Authentication)
Route::middleware('auth:api')->group(function () {
    // promote user to admin
    Route::post('/users/{id}/promote', [AuthController::class, 'promoteToAdmin']);
    // demote user to user
    Route::post('/users/{id}/demote', [AuthController::class, 'demoteToUser']);
    // promote user to manager
    Route::post('/users/{id}/promote/manager', [AuthController::class, 'promoteToManager']);
    // demote manager to user
    Route::post('/users/{id}/demote/manager', [AuthController::class, 'demoteManagerToUser']);

    Route::get('/items/{id}', [ItemController::class, 'show']); // Get a specific item
    Route::get('/useritems', [ItemController::class, 'useritems']); // Get a specific item

    Route::post('/items', [ItemController::class, 'store']); // Create an item
    Route::get('/users', action: [AuthController::class, 'userProfile']);
    Route::get('/users/{id}', action: [AuthController::class, 'getUserById']);
    Route::get('/user', action: [AuthController::class, 'getAllUsers']);
    Route::delete('/users/{id}', action: [AuthController::class, 'deleteUser']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/changePassword', [AuthController::class, 'changePassword'])->name('changePassword');
    Route::patch('/items/{id}', [ItemController::class, 'update']); // Patch update an item
    Route::delete('/items/{id}', [ItemController::class, 'destroy']); // Delete an item
    // optional route to mark item as taken
    Route::post('items/{id}/take', [ItemController::class, 'takeItem']);
});
