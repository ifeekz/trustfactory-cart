<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Products/Index');
})->name('products');

Route::get('/my-cart', function () {
    return Inertia::render('Cart/Show');
})->name('my-cart');

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'store']);
Route::patch('/cart/items/{product}', [CartController::class, 'update']);
Route::delete('/cart/items/{product}', [CartController::class, 'destroy']);

Route::middleware('auth')->group(function () {
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
