<?php

namespace App\Http\Controllers;

use App\helper\JWTToken;
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
        $count = User::where('email', $request->input('email'))
            ->where('password', $request->input('password'))
            ->select('id')
            ->first();

            if($count !== null){
                // user login jwt token issue
                $token = JWTToken::CreateToken($request->input('email'), $count->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User login successfully',
                    'token' => $token,
                ], 200)->cookie('token', $token, time()+60);
            }else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User login failed',
                ], 401);
            }// end condition

    }// end method for user login

    public function UserLogout(Request $request){
        return response()->json([
            'status' => 'success',
            'message' => 'User logout successfully',
        ], 200)->cookie('token', '', -1);
    }// end method for user logout
}
