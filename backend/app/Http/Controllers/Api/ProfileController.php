<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Kullanıcı bilgilerini döner
    public function profile(Request $request)
    {
        $user = $request->user(); // Token ile auth edilen kullanıcı
        return response()->json($user);
    }

    // Profil güncelleme
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:50',
        ]);

        $user->update($request->only('first_name', 'last_name', 'email', 'phone'));

        return response()->json([
            'status' => 'success',
            'message' => 'Profil başarıyla güncellendi.',
            'user' => $user
        ]);
    }

    // Şifre güncelleme
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mevcut şifre yanlış!'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi.'
        ]);
    }
}
