<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProblemController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/problems', [ProblemController::class, 'store']);
Route::get('/problems', [ProblemController::class, 'index']);
Route::put('/problems/{id}', [ProblemController::class, 'update']);


// routes/api.php
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/problems', [ProblemController::class, 'store']);
    // অন্যান্য protected routes
});
