<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BatchDashboardController;

    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('post.login');


Route::middleware(['auth'])->group(function () {
  
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('s.dashboard');

    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);
    Route::put('/products/{id}/status', [ProductController::class, 'updateStatus'])->name('products.updateStatus');
    Route::resource('orders', OrderController::class);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    //Route::get('/orders/{order}/batch/create', [BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    
    Route::get('/batches/create/{order}', [BatchController::class, 'create'])->name('batches.create');
        Route::get('/batches/{id}', [BatchController::class, 'show'])->name('batches.show');
    
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    

    Route::post('/sensors', [SensorController::class, 'store'])->name('sensors.store');
    Route::get('/sensors', [SensorController::class, 'index']);
    Route::get('/sensors/batch/{batchId}', [SensorController::class, 'getBatchSensors']);
    Route::get('/api/users/logistics', [UserController::class, 'getLogisticsUsers'])->name('users.logistics');
    
    Route::get('/batch', [BatchController::class, 'indexer'])->name('batches.eindex');

    Route::get('/batches/{batchId}/dashboard', [BatchDashboardController::class, 'show'])
    ->name('batch.show');
    Route::get('/batches/{batchId}/data', [BatchDashboardController::class, 'getData'])->name('batches.data');
    Route::get('/batches/{batchId}/sensors/{sensorId}', [BatchDashboardController::class, 'showSensor'])
        ->name('batch.sensor');

    Route::get('/batch/{batchId}', [BatchController::class, 'shows'])->name('batches.show');
    Route::patch('batches/{batch}/status', [BatchController::class, 'updateStatus'])->name('batches.update-status');

    Route::get('/batches/refresh', [BatchController::class, 'refresh'])->name('batches.refresh');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    Route::get('/batches/{batchId}/refresh', [BatchController::class, 'refreshBatch'])->name('batches.refresh.batch');
    Route::get('/batches/{batchId}/sensors/{sensorId}', [BatchController::class, 'showSensor'])->name('batch.sensor');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


});