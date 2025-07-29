<?php

use App\Http\Controllers\UserController;
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


