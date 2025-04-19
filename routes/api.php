<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TicketAPIController;
use App\Http\Controllers\API\BoardAPIController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for Tickets
Route::middleware('auth:sanctum')->group(function () {
    // Tickets API
    Route::get('/tickets', [TicketAPIController::class, 'index']);
    Route::post('/tickets', [TicketAPIController::class, 'store']);
    Route::get('/tickets/{id}', [TicketAPIController::class, 'show']);
    Route::put('/tickets/{id}', [TicketAPIController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketAPIController::class, 'destroy']);
    
    // Boards API
    Route::get('/boards', [BoardAPIController::class, 'index']);
    Route::post('/boards', [BoardAPIController::class, 'store']);
    Route::get('/boards/{id}', [BoardAPIController::class, 'show']);
    Route::put('/boards/{id}', [BoardAPIController::class, 'update']);
    Route::delete('/boards/{id}', [BoardAPIController::class, 'destroy']);
    
    // Board Tickets API
    Route::get('/boards/{id}/tickets', [BoardAPIController::class, 'tickets']);
    
    // Dashboard API
    Route::get('/dashboard/summary', [DashboardAPIController::class, 'summary']);
});