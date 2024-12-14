<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;



    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class);
    Route::get('/orders/{order}/batch/create', [BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');

//});