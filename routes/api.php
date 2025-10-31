<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProblemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Problem routes (all authenticated users)
    Route::get('/problems', [ProblemController::class, 'index']);
    Route::get('/problems/{id}', [ProblemController::class, 'show']);
    Route::post('/problems', [ProblemController::class, 'store']);
    Route::post('/problems/{id}/submit-solution', [ProblemController::class, 'submitSolution']);
    Route::post('/problems/{id}/verify', [ProblemController::class, 'verifySolution']);
    Route::put('/problems/{id}/status', [ProblemController::class, 'updateStatus']);

    // Admin & Team Leader routes
    Route::middleware('check.role:admin,team_leader')->group(function () {
        Route::post('/problems/{id}/assign', [ProblemController::class, 'assign']);
        Route::post('/problems/{id}/reassign', [ProblemController::class, 'reassign']);
        Route::post('/problems/{id}/close', [ProblemController::class, 'close']);
    });

    // Admin only routes
    Route::middleware('check.role:admin')->group(function () {
        // User management
        Route::get('/admin/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::post('/admin/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::delete('/admin/users/{id}/reject', [AdminController::class, 'rejectUser']);
        Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
        Route::put('/admin/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::put('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        
        // Dashboard & Reports
        Route::get('/admin/dashboard/stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/admin/reports/live', [AdminController::class, 'generateLiveReport']);
        
        // Problem management
        Route::delete('/problems/{id}', [ProblemController::class, 'destroy']);
    });
});