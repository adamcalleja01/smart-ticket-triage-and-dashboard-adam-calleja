<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\ClassifyTicketController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Get a list of tickets.
 */
Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

/**
 * Create a new ticket.
 */
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

/**
 * Get a specific ticket.
 */
Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');

/**
 * Update a specific ticket.
 */
Route::patch('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');

/**
 * Classify a specific ticket.
 */
Route::post('/tickets/{id}/classify', ClassifyTicketController::class)->name('tickets.classify');


/**
 *  Get application statistics.
 */
Route::get('/stats', StatsController::class)->name('stats');


