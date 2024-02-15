<?php

use App\Http\Controllers\Auth\AuthController;
use ECUApp\SharedCode\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post'); 
Route::get('registration', [AuthController::class, 'registration'])->name('register');
Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post'); 
Route::get('home', [AuthController::class, 'home'])->name('home'); 
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account');

Route::get('/file-upload', [App\Http\Controllers\FileController::class, 'index'])->name('file-upload');
Route::get('/file-history', [App\Http\Controllers\FileController::class, 'fileHistory'])->name('file-history');
Route::get('/bosch-ecu', [App\Http\Controllers\AccountController::class, 'boschECU'])->name('bosch-ecu');
Route::get('/evc_credit_shop', [App\Http\Controllers\EVCPackagesController::class, 'packages'])->name('evc-credits-shop');
Route::get('/shop-product', [App\Http\Controllers\PaymentController::class, 'shopProduct'])->name('shop-product');
Route::get('/price-list', [App\Http\Controllers\AccountController::class, 'priceList'])->name('price-list');
Route::get('/invoices', [App\Http\Controllers\InvoicesController::class, 'index'])->name('invoices');
Route::get('/cart', [App\Http\Controllers\PaymentController::class, 'cart'])->name('cart');
