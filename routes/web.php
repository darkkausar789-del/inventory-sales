<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Khusus Owner
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('products', ProductController::class)->except(['index', 'show']);
    Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
});

// Owner dan Staff (data internal)
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::resource('products', ProductController::class)->only(['index', 'show']);
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
});

// Semua peran yang login
Route::middleware(['auth', 'role:admin,staff,customer'])->group(function () {
    Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
});