<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TicketAPIController;
use App\Http\Controllers\API\BoardAPIController;
use App\Http\Controllers\API\DashboardAPIController;
use App\Http\Controllers\API\AuthController;

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

// Public API routes
Route::post('/token', [AuthController::class, 'token']);

// Web session authenticated users can use this to get an API token
Route::middleware('auth:web')->post('/token/generate', [AuthController::class, 'generateSessionToken']);

// Auth required API routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth management
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/token/revoke', [AuthController::class, 'revokeTokens']);
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