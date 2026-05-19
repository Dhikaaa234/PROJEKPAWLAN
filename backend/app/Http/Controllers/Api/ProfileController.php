<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => ApiFormatter::user($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'nama' => ['nullable', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:50', 'unique:users,nim,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ]);

        $updates = [];

        if ($request->has('name') || $request->has('nama')) {
            $name = $validated['name'] ?? $validated['nama'] ?? null;
            $updates['name'] = $name;
            $updates['nama'] = $name;
        }

        if ($request->has('nim')) {
            $updates['nim'] = $validated['nim'] ?? null;
        }

        if ($request->has('phone') || $request->has('no_telepon')) {
            $updates['no_telepon'] = $validated['phone'] ?? $validated['no_telepon'] ?? null;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'user' => ApiFormatter::user($user->fresh('role')),
            'data' => [
                'user' => ApiFormatter::user($user->fresh('role')),
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }
}
