<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Official routes - these will replace the mockups
Route::middleware(['auth'])->group(function () {
    // Tickets routes
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('tickets/{id}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    
    // Boards routes
    Route::get('boards', [BoardController::class, 'index'])->name('boards.index');
    Route::get('boards/create', [BoardController::class, 'create'])->name('boards.create');
    Route::post('boards', [BoardController::class, 'store'])->name('boards.store');
    Route::get('boards/{id}', [BoardController::class, 'show'])->name('boards.show');
    Route::get('boards/{id}/edit', [BoardController::class, 'edit'])->name('boards.edit');
    Route::put('boards/{id}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('boards/{id}', [BoardController::class, 'destroy'])->name('boards.destroy');
});

// Keep mockup routes temporarily for backward compatibility
Route::middleware(['auth'])->prefix('mockups')->name('mockups.')->group(function () {
    Route::get('ticket', function () {
        return Inertia::render('Mockups/Ticket');
    })->name('ticket');
    
    Route::get('tickets', function () {
        return Inertia::render('Mockups/Tickets');
    })->name('tickets');
    
    Route::get('boards', function () {
        return Inertia::render('Mockups/Boards');
    })->name('boards');
    
    Route::get('boards/{id}', function ($id) {
        return Inertia::render('Mockups/BoardDetail', ['boardId' => $id]);
    })->name('board.detail');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
