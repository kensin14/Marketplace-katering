<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\MerchantOrderController;
use App\Http\Controllers\MerchantProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['member.auth', 'role:merchant'])
    ->prefix('merchant')
    ->name('merchant.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [MerchantProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [MerchantProfileController::class, 'update'])->name('profile.update');
        Route::post('/manage', [ManageController::class, 'store'])->name('manage');
        Route::get('/orders', [MerchantOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/status', [MerchantOrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/invoice', [InvoiceController::class, 'merchant'])->name('orders.invoice');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
        Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    });
