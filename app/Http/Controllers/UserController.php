<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\MailOtp;
use App\helper\JWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

    public function DashboardPage(Request $request){
        $user = $request->header('email');
        return response()->json([
            'status' => 'success',
            'message' => 'User Login Successfully',
            'user' => $user
        ],200);
    }// end method for user dashboard

    public function SendOtpCode(Request $request){
        $email = $request->input('email');
        // send otp to user email
        $otp = rand(1000,9999);
        $count = User::where('email', $email)->count();

        if($count == 1){
            Mail::to($email)->send(new MailOtp($otp));
            User::where('email', $email)->update(['otp' => $otp]);
            return response()->json([
                'status' => 'success',
                'message' => "4 Digit {$otp}  Code has been sent to your email",
            ],200);
        }else{
            return response()->json([
                'status' => 'failed',
                'message' => 'Email not found',
            ],404);
        }
    }// end method for send otp

    public function VerifyOtp(Request $request){
        $email = $request->input('email');
        $otp = $request->input('otp');

        $count = User::where('email', $email)->where('otp', $otp)->count();
        $token = JWTToken::CreateTokenForSetPassword($email);

        if($count == 1){
            User::where('email', $email)->update(['otp' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ], 200)->cookie('token', $token, 60*24*30);
        }else{
            return response()->json([
                'status' => 'failed',
                'message' => 'OTP verified failed',
            ],401);
        }
    }// end method for verify otp

    public function ResetPassword(Request $request){
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', $email)->update(['password' => $password]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong, Please try again later',
            ],500);
        }
    }// end method for reset password

    public function UserUpdate(Request $request){
        $user_email = $request->header('email');
        $new_email = $request->input('email');

        $user = User::where('email', $user_email)->first();

        $user->update([
            'name' => $request->input('name'),
            'email' => $new_email,
            'phone' => $request->input('phone'),
        ]);

        if($user_email !== $new_email){
            return response()->json([
                'status' => 'Success',
                'message' => 'user updated successfully. You have been logged out due to email change.',
            ])->cookie('token', '', -1);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'user updated successfully.'
        ]);

    }// end method for user profile update
}
