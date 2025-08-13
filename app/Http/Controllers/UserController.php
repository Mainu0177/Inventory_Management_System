<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\MailOtp;
use App\helper\JWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

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

            // return response()->json([
            //     'status' => true,
            //     'message' => 'User Registration successfully',
            //     'data' => $user
            // ]);
            $data = [
                'status' => true,
                'message' => 'User Registration successfully',
                'error' => '',
            ];
            return redirect('/login')->with($data);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Registration failed',
            //     // 'message' => $e->getMessage(),
            // ],500);
            $data = [
                'status' => false,
                'message' => 'Registration failed',
                'error' => ''
            ];
            return redirect('/registration')->with($data);
        }
    } // end method for user registration

    public function UserLogin(Request $request){
        $count = User::where('email', $request->input('email'))
            ->where('password', $request->input('password'))
            ->select('id')
            ->first();

            if($count !== null){
                // user login jwt token issue
                // $token = JWTToken::CreateToken($request->input('email'), $count->id);

                $email = $request->input('email');
                $user_id = $count->id;


                // return response()->json([
                //     'status' => 'success',
                //     'message' => 'User login successfully',
                //     'token' => $token,
                // ], 200)->cookie('token', $token, time()+60);

                $request->session()->put('email', $email);
                $request->session()->put('user_id', $user_id);

                $data = [
                    'status' => true,
                    'message' => 'User login successfully',
                    'error' => '',
                ];
            return redirect('/dashboardPage')->with($data);

            }else{
                $data = [
                    'status' => false,
                    'message' => 'User login failed',
                    'error' => '',
                ];
            return redirect('login')->with($data);
            }// end condition

    }// end method for user login

    public function UserLogout(Request $request){
        return response()->json([
            'status' => 'success',
            'message' => 'User logout successfully',
        ], 200)->cookie('token', '', -1);
    }// end method for user logout

    public function SendOtpCode(Request $request){
        $email = $request->input('email');
        // send otp to user email
        $otp = rand(1000,9999);
        $count = User::where('email', $email)->count();

        if($count == 1){
            Mail::to($email)->send(new MailOtp($otp));
            User::where('email', $email)->update(['otp' => $otp]);

            $request->session()->put('email', $email);
            // return response()->json([
            //     'status' => 'success',
            //     'message' => "4 Digit {$otp}  Code has been sent to your email",
            // ],200);
            $data = [
                'status' => true,
                'message' => "4 Digit {$otp}  Code has been sent to your email",
                'error' => '',
            ];
            return redirect('/registration')->with($data);
        }else{
            // return response()->json([
            //     'status' => 'failed',
            //     'message' => 'Email not found',
            // ],404);
            $data = [
                'status' => false,
                'message' => 'Unauthorized email',
                'error' => '',
            ];
            return redirect('/send-otp')->with($data);
        }
    }// end method for send otp

    public function VerifyOtp(Request $request){
        // $email = $request->input('email');
        $email = $request->session()->get('email', 'default');
        $otp = $request->input('otp');

        $count = User::where('email', $email)->where('otp', $otp)->count();

        if($count == 1){
            User::where('email', $email)->update(['otp' => 0]);
            // $token = JWTToken::CreateTokenForSetPassword($email);

        $request->session()->put('otp_verify', 'yes');


            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'OTP verified successfully',
            // ], 200)->cookie('token', $token, 60*24*30);
            $data = [
                'status' => true,
                'message' => 'OTP verification successfully',
                'error' => '',
            ];
            return redirect('/reset-password')->with($data);
        }else{
            // return response()->json([
            //     'status' => 'failed',
            //     'message' => 'OTP verified failed',
            // ],401);
            $data = [
                'status' => false,
                'message' => 'OTP verification failed',
                'error' => ''
            ];
            return redirect('/login')->with($data);
        }
    }// end method for verify otp

    public function ResetPassword(Request $request){
        try {
            // $email = $request->header('email');
            $email = $request->session()->get('email', 'default');
            $password = $request->input('password');

            $otp_verify = $request->session()->get('otp_verify', 'default');
            if($otp_verify === 'yes'){
                User::where('email', $email)->update(['password' => $password]);
                $request->session()->flush();

                $data = [
                'status' => true,
                'message' => 'Password reset successfully',
                'error' => '',
            ];
                return redirect('/login')->with($data);
            }else{
                $data = [
                'status' => false,
                // 'message' => 'Something went wrong, Please try again later',
                'message' => 'Password does not reset, Please try again later',
                'error' => '',
            ];
                return redirect('/reset-password')->with($data);
            }

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'Password reset successfully',
            // ], 200);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => 'failed',
            //     'message' => 'Something went wrong, Please try again later',
            // ],500);
            $data = [
                'status' => false,
                'message' => 'Something went wrong, Please try again later',
                'error' => '',
            ];
                return redirect('/reset-password')->with($data);
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


    public function LoginPage(){
        return Inertia::render('LoginPage');
    }
    public function RegistrationPage(){
        return Inertia::render('RegistrationPage');
    }
    public function SendOtpPage(){
        return Inertia::render('SendOtpPage');
    }
    public function VerifyOtpPage(){
        return Inertia::render('VerifyOtpPage');
    }
    public function ResetPasswordPage(){
        return Inertia::render('ResetPasswordPage');
    }
}
