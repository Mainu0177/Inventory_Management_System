<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('HomePage');
    } // end method for home page
    public function homePage(){
        return Inertia::render('home');
    }
}
