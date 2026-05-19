<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    // Update profil (name, email, nim, no_telepon)
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:users,email,' . $user->id,
            'nim'        => 'nullable|string|max:50|unique:users,nim,' . $user->id,
            'no_telepon' => 'nullable|string|max:15',
            'phone'      => 'nullable|string|max:15',
        ]);

        if (isset($validated['name'])) {
            $validated['nama'] = $validated['name'];
            unset($validated['name']);
        }

        if (isset($validated['phone'])) {
            $validated['no_telepon'] = $validated['phone'];
            unset($validated['phone']);
        }

        unset($validated['phone']);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data'    => ['user' => $user->fresh()],
        ]);
    }

    // Ganti password
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Password lama tidak sesuai.']
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }
}