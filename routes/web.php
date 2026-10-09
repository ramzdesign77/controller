<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->role === 'admin') {
        return redirect()->route('products.index');
    }

    return redirect()->route('cashier.index');
})->name('home');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::resource('products', ProductController::class)
    ->middleware(['auth', 'role:admin']);

Route::middleware(['auth', 'role:kasir'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/products/search', [CashierController::class, 'search'])->name('products.search');
    Route::post('/transactions', [CashierController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/resume', [CashierController::class, 'resume'])->name('resume');
    Route::delete('/transactions/{transaction}', [CashierController::class, 'destroyHeld'])->name('transactions.destroy');
    Route::get('/transactions/{transaction}/receipt', [CashierController::class, 'receipt'])->name('receipt');
});
