<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['member.auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/', [CustomerController::class, 'catalog'])->name('catalog');
        Route::post('/cart', [CustomerController::class, 'addToCart'])->name('cart.add');
        Route::get('/cart', [CustomerController::class, 'cart'])->name('cart');
        Route::delete('/cart/{product}', [CustomerController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
        Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}/invoice', [InvoiceController::class, 'customer'])->name('orders.invoice');
    });
