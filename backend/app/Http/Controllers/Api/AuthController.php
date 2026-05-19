<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['nullable', 'required_without:name', 'string', 'max:100'],
            'name' => ['nullable', 'required_without:nama', 'string', 'max:100'],
            'nim' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', PasswordRule::min(6)],
        ]);

        $name = $validated['nama'] ?? $validated['name'] ?? null;
        $userRole = Role::where('name', 'user')->firstOrFail();

        $user = User::create([
            'nama' => $name,
            'name' => $name,
            'nim' => $validated['nim'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $userRole->id,
        ]);

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => ApiFormatter::user($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => ApiFormatter::user($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => ApiFormatter::user($request->user()),
        ]);
    }

    /**
     * Kirim link reset password ke email user.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status)
            ], 200);
        }

        return response()->json([
            'message' => __($status)
        ], 400);
    }

    /**
     * Reset password menggunakan token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status)
            ], 200);
        }

        return response()->json([
            'message' => __($status)
        ], 400);
    }
}