<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/{ticket:id}', [TicketController::class, 'show'])->name('tickets.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');



require __DIR__.'/auth.php';
