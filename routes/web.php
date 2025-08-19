<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SessionAuthenticate;
use App\Http\Middleware\TokenVerificationMiddleware;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    // return view('welcome');
    return Inertia::render('TestPage');
});

Route::get('/', [HomeController::class, 'index'])->name('home.Page');
Route::get('/Home', [HomeController::class, 'HomePage'])->name('Home');


// All user Routes
Route::post('/user-registration', [UserController::class, 'UserRegistration'])->name('UserRegistration');
Route::post('/user-login', [UserController::class, 'UserLogin'])->name('user.login');
// Route::get('/user-logout', [UserController::class, 'UserLogout'])->name('user.logout');
Route::get('/dashboardPage', [DashboardController::class, 'DashboardPage'])->middleware([SessionAuthenticate::class])->name('dashboard.page');

//send otp
Route::post('/send-otp', [UserController::class, 'SendOtpCode'])->name('send.otp');
Route::post('/verify-otp', [UserController::class, 'VerifyOtp'])->name('verify.otp');
Route::post('/reset-password', [UserController::class, 'ResetPassword'])->middleware([SessionAuthenticate::class]);

// Make Route group
Route::middleware(SessionAuthenticate::class)->group(function () {
    // Category all routes
    Route::controller(CategoryController::class)->group(function () {
        Route::post('/create-category', 'CreateCategory')->name('category.create');
        Route::get('/list-category', 'ListCategory')->name('list.create');
        Route::post('/category-details-by-id', 'CategoryDetailsById')->name('category.details');
        Route::post('/update-category', 'UpdateCategory')->name('category.update');
        // Route::delete('/delete-category', 'DeleteCategory')->name('category.delete');
        Route::get('/delete-category/{id}', 'DeleteCategory')->name('category.delete');

        Route::get('/categoryPage', 'CategoryPage')->name('category.page');
        Route::get('/CategorySavePage', 'CategorySavePage')->name('CategorySavePage');
    });

    // Product all routes
    Route::controller(ProductController::class)->group(function () {
        Route::post('/create-product', 'CreateProduct')->name('product.create');
        Route::get('/product-list', 'ProductList')->name('product.list');
        Route::post('/product-detail-by-id', 'ProductDetail')->name('product.detail.by.id');
        Route::post('/product-update', 'ProductUpdate')->name('product.update');
        Route::get('/product-delete/{id}', 'ProductDelete')->name('product.delete');

        Route::get('/ProductPage', 'ProductPage')->name('productPage');
        Route::get('/ProductSavePage', 'productSavePage')->name('productSavePage');

    });

    // Customer all routes
    Route::controller(CustomerController::class)->group(function () {
        Route::post('/create-customer', 'CreateCustomer')->name('customer.create');
        Route::get('/customer-list', 'CustomerList')->name('customer.list');
        Route::post('/customer-detail-by-id', 'CustomerDetail')->name('customer.detail.by.id');
        Route::post('/customer-update', 'CustomerUpdate')->name('customer.update');
        Route::get('/customer-delete/{id}', 'CustomerDelete')->name('customer.delete');

        Route::get('/CustomerPage', 'CustomerPage')->name('CustomerPage');
        Route::get('/CustomerSavePage', 'CustomerSavePage')->name('CustomerSavePage');

    });

    // Invoice all routes
    Route::controller(InvoiceController::class)->group(function () {
        Route::post('/create-invoice', 'CreateInvoice')->name('create.invoice');
        Route::get('/invoice-list', 'InvoiceList')->name('invoice.list');
        Route::post('/invoice-details', 'InvoiceDetails')->name('invoice.details');
        Route::get('/invoice-delete/{id}', 'InvoiceDelete')->name('invoice.delete');

        Route::get('/InvoiceListPage', 'InvoiceListPage')->name('InvoicePage');
        Route::get('/InvoiceSavePage', 'InvoiceSavePage')->name('InvoiceSavePage');
    });

    // Dashboard Summary
    Route::get('/dashboard-summary', [DashboardController::class, 'DashboardController'])->name('dashboard.summary');

    // Profile update
    Route::post('/user-profile-update', [UserController::class, 'UserUpdate'])->name('dashboard.summary');
    Route::get('/userProfilePage', [UserController::class, 'UserProfilePage'])->name('ProfilePage');
    Route::get('/user-logout', [UserController::class, 'UserLogout'])->name('user.logout');


    //sale all routes
    Route::get('/create-sale', [SaleController::class, 'SalePage'])->name('sale.page');



});

// Front end all route
Route::get('/login', [UserController::class, 'LoginPage'])->name('login.page');
Route::get('/registration', [UserController::class, 'RegistrationPage'])->name('RegistrationPage');
Route::get('/send-otp', [UserController::class, 'SendOtpPage'])->name('SendOtpPage');
Route::get('/verify-otp', [UserController::class, 'VerifyOtpPage'])->name('VerifyOtpPage');
Route::get('/reset-password', [UserController::class, 'ResetPasswordPage'])->name('ResetPasswordPage')->middleware([SessionAuthenticate::class]);
