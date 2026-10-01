<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tickets')->name('home');

Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
    ->name('tickets.status.update');
Route::resource('tickets', TicketController::class)->except(['destroy']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
