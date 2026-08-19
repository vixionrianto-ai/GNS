<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        ApiToken::where('user_id', $user->id)->where('name', 'gns-android')->delete();

        $plainToken = Str::random(80);
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'gns-android',
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $plainToken,
                'user' => $user,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil dimuat.',
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $plainToken = trim(substr($request->header('Authorization', ''), 7));
        ApiToken::where('token_hash', hash('sha256', $plainToken))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}
