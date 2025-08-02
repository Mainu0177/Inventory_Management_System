<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVerificationMiddleware;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    // return view('welcome');
    return Inertia::render('TestPage');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');


// All user Routes
Route::post('/user-registration', [UserController::class, 'UserRegistration'])->name('UserRegistration');
Route::post('/user-login', [UserController::class, 'UserLogin'])->name('user.login');
Route::get('/user-logout', [UserController::class, 'UserLogout'])->name('user.logout');
Route::get('/dashboardPage', [UserController::class, 'DashboardPage'])->middleware([TokenVerificationMiddleware::class])->name('dashboard.page');

//send otp
Route::post('/send-otp', [UserController::class, 'SendOtpCode'])->name('send.otp');
Route::post('/verify-otp', [UserController::class, 'VerifyOtp'])->name('verify.otp');
Route::post('/reset-password', [UserController::class, 'ResetPassword'])->middleware([TokenVerificationMiddleware::class]);

// Make Route group
Route::middleware(TokenVerificationMiddleware::class)->group(function () {
    // Category all routes
    Route::controller(CategoryController::class)->group(function () {
        Route::post('/create-category', 'CreateCategory')->name('category.create');
        Route::get('/list-category', 'ListCategory')->name('list.create');
        Route::post('/category-details-by-id', 'CategoryDetailsById')->name('category.details');
        Route::post('/update-category', 'UpdateCategory')->name('category.update');
        // Route::delete('/delete-category', 'DeleteCategory')->name('category.delete');
        Route::get('/delete-category/{id}', 'DeleteCategory')->name('category.delete');
    });

    // Product all routes
    Route::controller(ProductController::class)->group(function () {
        Route::post('/create-product', 'CreateProduct')->name('product.create');
        Route::get('/product-list', 'ProductList')->name('product.list');
        Route::post('/product-detail-by-id', 'ProductDetail')->name('product.detail.by.id');
        Route::post('/product-update', 'ProductUpdate')->name('product.update');
        Route::get('/product-delete/{id}', 'ProductDelete')->name('product.delete');
    });

    // Customer all routes
    Route::controller(CustomerController::class)->group(function () {
        Route::post('/create-customer', 'CreateCustomer')->name('customer.create');
        Route::get('/customer-list', 'CustomerList')->name('customer.list');
        Route::post('/customer-detail-by-id', 'CustomerDetail')->name('customer.detail.by.id');
        Route::post('/customer-update', 'CustomerUpdate')->name('customer.update');
        Route::delete('/customer-delete', 'CustomerDelete')->name('customer.delete');
    });

    // Invoice all routes
    Route::controller(InvoiceController::class)->group(function () {
        Route::post('/create-invoice', 'CreateInvoice')->name('create.invoice');
    });
});
