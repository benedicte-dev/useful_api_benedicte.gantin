<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Middleware\CheckModuleActive;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProductController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées avec auth seulement
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Gestion des modules
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::post('/modules/{id}/activate', [ModuleController::class, 'activate']);
    Route::post('/modules/{id}/deactivate', [ModuleController::class, 'deactivate']);

    // Module 1:  URL Shortener avec middleware direct
    Route::post('/shorten', [ShortLinkController::class, 'shorten'])
        ->middleware(CheckModuleActive::class . ':1');

    Route::get('/links', [ShortLinkController::class, 'index'])
        ->middleware(CheckModuleActive::class . ':1');

    Route::delete('/links/{id}', [ShortLinkController::class, 'destroy'])
        ->middleware(CheckModuleActive::class . ':1');
});

// Module 2:  Wallet
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])
        ->middleware(CheckModuleActive::class . ':2');

    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])
        ->middleware(CheckModuleActive::class . ':2');

    Route::post('/wallet/topup', [WalletController::class, 'topup'])
        ->middleware(CheckModuleActive::class . ':2');

    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])
        ->middleware(CheckModuleActive::class . ':2');
});

// Module 3 - Marketplace
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware(CheckModuleActive::class . ':3');

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware(CheckModuleActive::class . ':3');

    Route::post('/orders', [ProductController::class, 'createOrder'])
        ->middleware(CheckModuleActive::class . ':3');

    Route::post('/products/{id}/restock', [ProductController::class, 'restock'])
        ->middleware(CheckModuleActive::class . ':3');

    Route::get('/orders', [ProductController::class, 'orders'])
        ->middleware(CheckModuleActive::class . ':3');
});

// Route publique de redirection (sans authentification)
Route::get('/s/{code}', [ShortLinkController::class, 'redirect']);
