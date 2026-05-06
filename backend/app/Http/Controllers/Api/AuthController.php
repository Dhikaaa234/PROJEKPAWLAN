<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Log;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 2 // default user
        ]);

        Log::create([
            'user_id' => $user->id,
            'aktivitas' => 'User melakukan registrasi'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::create([
            'user_id' => $user->id,
            'aktivitas' => 'User login ke sistem'
        ]);

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        Log::create([
            'user_id' => $request->user()->id,
            'aktivitas' => 'User logout dari sistem'
        ]);

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    // GET CURRENT USER
    public function me(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }

    // FORGOT PASSWORD
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // For security, don't reveal if email exists or not
            return response()->json([
                'message' => 'Jika akun dengan email ini ada, link reset akan dikirim'
            ]);
        }

        // TODO: Implement actual password reset email functionality
        // For now, just return success message
        // In production, you would:
        // 1. Generate a token
        // 2. Store it in database with expiration
        // 3. Send email with reset link

        Log::create([
            'user_id' => $user->id,
            'aktivitas' => 'User meminta reset password'
        ]);

        return response()->json([
            'message' => 'Jika akun dengan email ini ada, link reset akan dikirim'
        ]);
    }
}
