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

// Basic user info route (no sanctum auth required, uses web session)
Route::middleware('auth')->get('/user-basic', function (Request $request) {
    return response()->json($request->user());
});

// Web session authenticated users can use this to get an API token
// Using 'auth:web' middleware for session auth
Route::middleware('auth:web')->post('/token/generate', [AuthController::class, 'generateSessionToken']);

// Debug routes - for testing only
if (app()->environment('local')) {
    // Session authentication check
    Route::get('/debug/session-info', function (Request $request) {
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ] : null,
            'session' => [
                'has_session' => session()->isStarted(),
                'token' => csrf_token(),
            ]
        ]);
    });
    
    // Test route that doesn't require authentication
    Route::get('/debug/public', function () {
        return response()->json([
            'message' => 'This is a public endpoint',
            'time' => now()->toIso8601String()
        ]);
    });
    
    // Test route that requires Sanctum authentication
    Route::middleware('auth:sanctum')->get('/debug/auth', function (Request $request) {
        return response()->json([
            'message' => 'You are authenticated!',
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ] : null
        ]);
    });
}

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