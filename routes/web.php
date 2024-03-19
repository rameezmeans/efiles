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

Route::get('pdfview',array('as'=>'pdfview','uses'=>'App\Http\Controllers\InvoicesController@makePDF'));

Route::get('/upload', [App\Http\Controllers\FileController::class, 'step1'])->name('upload');
Route::get('/history', [App\Http\Controllers\FileController::class, 'fileHistory'])->name('history');
Route::post('/step2', [App\Http\Controllers\FileController::class, 'step2'])->name('step2');
Route::get('/terms_and_conditions', [App\Http\Controllers\FileController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/norefund_policy', [App\Http\Controllers\FileController::class, 'norefundPolicy'])->name('norefund-policy');
Route::post('/create-temp-file', [App\Http\Controllers\FileController::class, 'createTempFile'])->name('create-temp-file');
Route::post('/get_models', [App\Http\Controllers\FileController::class, 'getModels'])->name('get-models');
Route::post('/get_versions', [App\Http\Controllers\FileController::class, 'getVersions'])->name('get-versions');
Route::post('/get_engines', [App\Http\Controllers\FileController::class, 'getEngines'])->name('get-engines');
Route::post('/get_ecus', [App\Http\Controllers\FileController::class, 'getECUs'])->name('get-ecus');
Route::get('/stages', [App\Http\Controllers\FileController::class, 'step3'])->name('step3');
Route::post('/post_stages', [App\Http\Controllers\FileController::class, 'postStages'])->name('post-stages');
Route::post('get_upload_comments', [App\Http\Controllers\FileController::class, 'getUploadComments'])->name('get-upload-comments');
Route::post('/get_options_for_stage', [App\Http\Controllers\FileController::class, 'getOptionsForStage'])->name('get-options-for-stage');
Route::post('/add_credits_to_file', [App\Http\Controllers\FileController::class, 'saveFile'])->name('add-credits-to-file');
Route::get('/file/{id}', [App\Http\Controllers\FileController::class, 'showFile'])->name('file');
Route::get('auto_download', [App\Http\Controllers\FileController::class, 'autoDownload'])->name('auto-download');
Route::post('/file_checkout', [App\Http\Controllers\PaymentsController::class, 'fileCart'])->name('checkout-file');
Route::post('/checkout_file', [App\Http\Controllers\PaymentsController::class, 'checkoutFile'])->name('checkout.stripe.file');
// Route::post('/checkout_offer_redirect', [App\Http\Controllers\FileController::class, 'checkoutOfferRedirect'])->name('checkout.stripe.offer');
// Route::post('/checkout_offer_paypal_redirect', [App\Http\Controllers\FileController::class, 'checkoutOfferPaypalRedirect'])->name('checkout.paypal.offer');
Route::post('/checkout_file_paypal', [App\Http\Controllers\FileController::class, 'checkoutFile'])->name('checkout.paypal.file');
Route::post('/request-file', [App\Http\Controllers\FileController::class, 'requestFile'])->name('request-file');
Route::post('/file-url', [App\Http\Controllers\FileController::class, 'fileURL'])->name('file-url');
Route::get('/download/{id}/{file}', [App\Http\Controllers\FileController::class,'download'])->name('download');
Route::post('/edit-milage', [App\Http\Controllers\FileController::class, 'EditMilage'])->name('edit-milage');
Route::post('/add-customer-note', [App\Http\Controllers\FileController::class, 'addCustomerNote'])->name('add-customer-note');

Route::get('/bosch-ecu', [App\Http\Controllers\AccountController::class, 'boschECU'])->name('bosch-ecu');
Route::get('/evc_credit_shop', [App\Http\Controllers\EVCPackagesController::class, 'packages'])->name('evc-credits-shop');
Route::post('buy_evc_package', [App\Http\Controllers\EVCPackagesController::class, 'buyEVCPackage'])->name('buy.evc.package');
Route::get('/evc_history', [App\Http\Controllers\EVCPackagesController::class, 'history'])->name('evc-history');
Route::post('checkout_evc_packages', [App\Http\Controllers\EVCPackagesController::class, 'checkoutEVCPackages'])->name('checkout.evc.packages');
Route::post('checkout_evc_packages_paypal', [App\Http\Controllers\EVCPackagesController::class, 'checkoutEVCPackages'])->name('checkout.evc.packages.paypal');
Route::get('cancel_evc_packages', [App\Http\Controllers\EVCPackagesController::class, 'cancelEVCPackages'])->name('checkout.evc.cancel');
Route::get('success_evc_packages', [App\Http\Controllers\EVCPackagesController::class, 'successEVC'])->name('checkout.evc.success');
// Route::get('success_evc_packages_paypal', [App\Http\Controllers\EVCPackagesController::class, 'successEVC'])->name('checkout.evc.success');

Route::get('/invoices', [App\Http\Controllers\InvoicesController::class, 'index'])->name('invoices');

Route::get('/shop-product', [App\Http\Controllers\PaymentsController::class, 'shopProduct'])->name('shop-product');
Route::get('/cart', [App\Http\Controllers\PaymentsController::class, 'cart'])->name('cart');
Route::post('/add_to_cart', [App\Http\Controllers\PaymentsController::class, 'addToCart'])->name('add-to-cart');
Route::post('buy_package', [App\Http\Controllers\PaymentsController::class, 'buyPackage'])->name('buy.package');
Route::post('/cart_quantity', [App\Http\Controllers\PaymentsController::class, 'getCartQuantity'])->name('get-cart');
Route::get('/cart', [App\Http\Controllers\PaymentsController::class, 'cart'])->name('cart');
Route::post('checkout_packages', [App\Http\Controllers\PaymentsController::class, 'checkoutPackagesStripe'])->name('checkout.packages.stripe');
Route::post('checkout_packages_paypal', [App\Http\Controllers\PaymentsController::class, 'checkoutPackagesPaypal'])->name('checkout.packages.paypal');

Route::post('/checkout_stripe', [App\Http\Controllers\PaymentsController::class, 'stripeCheckout'])->name('checkout.stripe');
Route::post('/checkout_paypal', [App\Http\Controllers\PaymentsController::class, 'paypalCheckout'])->name('checkout.paypal');
Route::get('/success', [App\Http\Controllers\PaymentsController::class, 'success'])->name('checkout.success');
Route::get('/success_package', [App\Http\Controllers\PaymentsController::class, 'successPackage'])->name('checkout.success.package');
Route::get('/cancel', [App\Http\Controllers\PaymentsController::class, 'cancel'])->name('checkout.cancel');

Route::post('get_tool_icons', [App\Http\Controllers\AccountController::class, 'getToolsIcons'])->name('get-tool-icons');

Route::get('/price-list', [App\Http\Controllers\PricelistController::class, 'index'])->name('price-list');

