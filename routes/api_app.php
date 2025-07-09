<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppAuthController;
use App\Http\Controllers\{
    StoreCategoryController,
    StoreController,
    ProductCategoryController,
    ProductController,
    UnitController,
    BannerController,
    CartOrderController
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AppAuthController::class, 'login']);
Route::post('/register', [AppAuthController::class, 'register']);
Route::post('/forgot-password', [AppAuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AppAuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AppAuthController::class, 'resetPassword']);


Route::middleware(['auth:api'])->group(function () {
    Route::get('/store-categories/{id?}', [StoreCategoryController::class, 'index']);
    Route::get('user/profile', [AppAuthController::class, 'getProfile']);
    Route::post('user/profile/update', [AppAuthController::class, 'updateProfile']);
    Route::post('user/profile/upload-image', [AppAuthController::class, 'uploadProfileImage']);

});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/store-categories/{id}/with-stores', [StoreCategoryController::class, 'showWithStores']);
    Route::get('/product-categories/{id}/with-products', [ProductCategoryController::class, 'showWithProducts']);

});


Route::middleware(['auth:api'])->group(function () {
    Route::get('/product-categories/with-products', [ProductCategoryController::class, 'allCategoriesWithProducts']);
    Route::get('/product-categories/{id?}', [ProductCategoryController::class, 'index']);

});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/products/{id?}', [ProductController::class, 'index']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/banners', [BannerController::class, 'index']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/units/{id?}', [UnitController::class, 'index']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/stores/{id?}', [StoreController::class, 'index']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/cart/add-multiple', [CartOrderController::class, 'addMultipleToCart']);
    Route::get('/cart', [CartOrderController::class, 'getCart']);
    Route::put('/cart/update/{id}', [CartOrderController::class, 'updateCartItem']);
    Route::delete('/cart/remove/{id}', [CartOrderController::class, 'removeCartItem']);
    Route::delete('/cart/clear', [CartOrderController::class, 'clearCart']);
    Route::post('/checkout', [CartOrderController::class, 'checkout']);


});
