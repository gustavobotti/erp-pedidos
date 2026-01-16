<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/dashboard/refresh', [DashboardController::class, 'refresh'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.refresh');

Route::get('/api-tests', function () {
    return Inertia::render('ApiTests');
})->middleware(['auth', 'verified'])->name('api-tests');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/products-import', [ProductController::class, 'showImport'])->name('products.import');
    Route::post('/products-import', [ProductController::class, 'import'])->name('products.import.process');

    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class);
    Route::get('/api/suppliers/{supplier}/products', [OrderController::class, 'getProductsBySupplier'])->name('suppliers.products');
    Route::post('/orders/send-daily-report', [OrderController::class, 'sendDailyReport'])->name('orders.send-daily-report');

    // Apenas admin
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('admin')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::post('/fetch-address', [SupplierController::class, 'fetchAddress'])->name('fetch-address');
    });
});

require __DIR__.'/auth.php';
