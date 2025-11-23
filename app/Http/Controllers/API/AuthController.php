<?php

namespace App\Http\Controllers\API;

use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $otpCode = rand(100000, 999999);
            Otp::create([
                'user_id' => $user->id,
                'otp_code' => Hash::make($otpCode),
                'expire_at' => now()->addMinutes(5),
            ]);

            DB::commit();


            Mail::to($user->email)->send(new OtpVerificationMail($otpCode));

            return response()->json([
                'status' => 'Success',
                'status_code' => 201,
                'message' => 'User registered successfully. An OTP has been sent to your email for verification.',
                'user_id' => $user->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'status' => 'Error',
                'status_code' => 500,
                'message' => 'Registration failed due to an internal server error.',
            ], 500);
        }
    }


    // public function login(Request $request){
    //     $credentials = $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //     ]);

    //     if (!Auth::attempt($credentials)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     $user = Auth::user();

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login successful',
    //         'user' => $user,
    //         'token' => $token,
    //     ]);
    // }


    // public function verifyEmail(Request $request){

    // }


    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json(['message' => 'Logged out successfully']);
    // }
}
