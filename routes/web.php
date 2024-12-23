<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('post.login');


Route::middleware(['auth'])->group(function () {
  
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('s.dashboard');

    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);
    Route::put('/products/{id}/status', [ProductController::class, 'updateStatus'])->name('products.updateStatus');
    Route::resource('orders', OrderController::class);
    Route::get('/orders/{order}/batch/create', [BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    // routes/web.php
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/{batchId}', [BatchController::class, 'show'])->name('batches.show');
    Route::get('/batches/refresh', [BatchController::class, 'refresh'])->name('batches.refresh');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    Route::get('/batches/{batchId}/refresh', [BatchController::class, 'refreshBatch'])->name('batches.refresh.batch');
    Route::get('/batches/{batchId}/sensors/{sensorId}', [BatchController::class, 'sensorReadings'])->name('batches.sensor.readings');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


});