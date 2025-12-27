<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Products/Index');
})->name('products');

Route::get( '/cart', function() {
    return Inertia::render('Cart/Show');
})->name('cart');

Route::get('/cart-data', [CartController::class, 'show']);
Route::post('/cart-data/items', [CartController::class, 'store']);
Route::patch('/cart-data/items/{product}', [CartController::class, 'update']);
Route::delete('/cart-data/items/{product}', [CartController::class, 'destroy']);

Route::middleware('auth')->group(function () {
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
