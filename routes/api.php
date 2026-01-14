<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/autenticacao', [AuthController::class, 'login'])->name('api.v1.auth.login');
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

    // Orders by supplier CNPJ
    Route::get('/fornecedor/{cnpj}/pedidos', [OrderApiController::class, 'getOrdersBySupplierCnpj'])
        ->name('api.v1.supplier.orders');

    // Orders CRUD
    Route::get('/pedidos/{id}', [OrderApiController::class, 'show'])->name('api.v1.orders.show');
    Route::post('/pedidos', [OrderApiController::class, 'store'])->name('api.v1.orders.store');
    Route::put('/pedidos/{id}', [OrderApiController::class, 'update'])->name('api.v1.orders.update');
    Route::delete('/pedidos/{id}', [OrderApiController::class, 'destroy'])->name('api.v1.orders.delete');
});
