<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function UserRegistration(Request $request){
        // dd($request->all());

        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required',
                'password' => 'required',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Registration successfully',
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
            ],500);
        }
    } // end method for user registration

    public function UserLogin(Request $request){
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
