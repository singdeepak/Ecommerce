<?php

namespace App\Http\Controllers\API;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
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
            return response()->json([
                'status' => 'Error',
                'status_code' => 500,
                'message' => 'Registration failed due to an internal server error.',
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|digits:6',
        ]);

        $user = User::find($request->user_id);

        $otpRecord = Otp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['status' => 'Error', 'message' => 'OTP record not found for this user.'], 404);
        }

        if ($otpRecord->expire_at < now()) {
            $otpRecord->delete();
            return response()->json(['status' => 'Error', 'message' => 'OTP has expired. Please request a new one.'], 401);
        }

        if (!Hash::check($request->otp_code, $otpRecord->otp_code)) {
            return response()->json(['status' => 'Error', 'message' => 'Invalid OTP code provided.'], 401);
        }

        $user->update(['email_verified_at' => now()]);
        $otpRecord->delete();

        return response()->json([
            'status' => 'Success',
            'status_code' => 200,
            'message' => 'Email verified successfully!',
            'user' => $user,
        ], 200);
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        $token = $user->createToken($request->header('User-Agent') ?? 'Web Browser')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json(
                ['message' => 'Logged out successfully'],
                200
            );
        }

        return response()->json(
            ['message' => 'No active session or token found'],
            401 
        );
    }
}
