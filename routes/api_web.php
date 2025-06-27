<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\{
    StoreCategoryController,
    StoreController,
    ProductCategoryController,
    ProductController,
    UnitController,
    BannerController,
    VoucherController
};


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


Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/register', [WebAuthController::class, 'register']);

// Route::middleware(['auth:api'])->group(function () {
//     Route::get('/store-categories/{id?}', [StoreCategoryController::class, 'index']);
// });

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/store-categories/{id?}', [StoreCategoryController::class, 'index']);
    Route::post('/store-categories', [StoreCategoryController::class, 'store']);
    Route::get('/store-categories/{id}', [StoreCategoryController::class, 'show']);
    Route::post('/store-categories/{id}', [StoreCategoryController::class, 'update']);
    Route::delete('/store-categories/{id}', [StoreCategoryController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/product-categories/{id?}', [ProductCategoryController::class, 'index']);
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
    Route::post('/product-categories/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/products/{id?}', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/banners', [BannerController::class, 'index']);
    Route::post('/banners', [BannerController::class, 'store']);
    Route::get('/banners/{id}', [BannerController::class, 'show']);
    Route::post('/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
    Route::post('/banners/{id}/status', [BannerController::class, 'changeStatus']);
});

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/units/{id?}', [UnitController::class, 'index']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::post('/units/{id}', [UnitController::class, 'update']);
    Route::delete('/units/{id}', [UnitController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::get('/stores/{id?}', [StoreController::class, 'index']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);
    Route::post('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);
});
Route::middleware(['auth:api', 'role:seller'])->group(function () {
    Route::post('/vouchers', [VoucherController::class, 'store']);
    Route::get('/vouchers', [VoucherController::class, 'index']);
});


