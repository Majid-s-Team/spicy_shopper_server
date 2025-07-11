<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::post('/web-login', [AuthController::class, 'webLogin']);
Route::get('/dashboard', function () {
    return 'Dashboard (Web)';
})->middleware(['auth', 'role:superadmin|seller']);
