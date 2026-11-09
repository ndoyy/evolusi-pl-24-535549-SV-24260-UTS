<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // Gagal validasi -> otomatis 422 (JSON) dan dicatat di bootstrap/app.php
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Email dan password sengaja tidak dicatat (data pribadi/rahasia).
            Log::warning('Login gagal', [
                'user_id' => $user?->id,
                'aksi' => 'login',
                'sebab' => 'email atau password tidak cocok',
            ]);

            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        Log::info('Login berhasil', [
            'user_id' => $user->id,
            'aksi' => 'login',
        ]);

        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        Log::info('Logout berhasil', [
            'user_id' => $userId,
            'aksi' => 'logout',
        ]);

        return response()->json(['message' => 'Logout berhasil.'], 200);
    }
}