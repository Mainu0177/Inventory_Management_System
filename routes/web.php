<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVeryficationMiddleware;
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
Route::get('/dashboardPage', [UserController::class, 'DashboardPage'])->middleware([TokenVeryficationMiddleware::class])->name('dashboard.page');

//send otp
Route::post('/send-otp', [UserController::class, 'SendOtpCode'])->name('send.otp');
Route::post('/verify-otp', [UserController::class, 'VerifyOtp'])->name('verify.otp');
Route::post('/reset-password', [UserController::class, 'ResetPassword'])->middleware([TokenVeryficationMiddleware::class]);


