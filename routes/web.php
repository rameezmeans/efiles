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
Route::get('register', [AuthController::class, 'registration'])->name('register');
Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post'); 
Route::get('home', [AuthController::class, 'home'])->name('home'); 
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account');
Route::post('/edit_account', [App\Http\Controllers\AccountController::class, 'editAccount'])->name('edit-account');
Route::post('/change-password', [App\Http\Controllers\AccountController::class, 'changePassword'])->name('change-password');
Route::post('/update_tools', [App\Http\Controllers\AccountController::class, 'updateTools'])->name('update-tools');
Route::post('get_tool_icons', [App\Http\Controllers\AccountController::class, 'getToolsIcons'])->name('get-tool-icons');

Route::get('/file-upload', [App\Http\Controllers\FileController::class, 'index'])->name('file-upload');
Route::get('/file-history', [App\Http\Controllers\FileController::class, 'fileHistory'])->name('file-history');
Route::get('/bosch-ecu', [App\Http\Controllers\AccountController::class, 'boschECU'])->name('bosch-ecu');
Route::get('/evc_credit_shop', [App\Http\Controllers\EVCPackagesController::class, 'packages'])->name('evc-credits-shop');

Route::get('/invoices', [App\Http\Controllers\InvoicesController::class, 'index'])->name('invoices');

Route::get('/shop-product', [App\Http\Controllers\PaymentsController::class, 'shopProduct'])->name('shop-product');
Route::get('/cart', [App\Http\Controllers\PaymentsController::class, 'cart'])->name('cart');
Route::post('/add_to_cart', [App\Http\Controllers\PaymentsController::class, 'addToCart'])->name('add-to-cart');
Route::post('buy_package', [App\Http\Controllers\PaymentsController::class, 'buyPackage'])->name('buy.package');
Route::post('/cart_quantity', [App\Http\Controllers\PaymentsController::class, 'getCartQuantity'])->name('get-cart');
Route::get('/cart', [App\Http\Controllers\PaymentsController::class, 'cart'])->name('cart');

Route::post('/checkout_stripe', [App\Http\Controllers\PaymentsController::class, 'stripeCheckout'])->name('checkout.stripe');
Route::post('/checkout_paypal', [App\Http\Controllers\PaymentsController::class, 'paypalCheckout'])->name('checkout.paypal');
Route::get('/success', [App\Http\Controllers\PaymentsController::class, 'successStripe'])->name('checkout.success.stripe');
Route::get('/cancel', [App\Http\Controllers\PaymentsController::class, 'cancel'])->name('checkout.cancel');

Route::post('get_tool_icons', [App\Http\Controllers\AccountController::class, 'getToolsIcons'])->name('get-tool-icons');

Route::get('/price-list', [App\Http\Controllers\PricelistController::class, 'index'])->name('price-list');

