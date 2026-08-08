<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{

    #[OA\Post(
        path: '/api/login',
        operationId: 'loginOperator',
        tags: ['Authentication'],
        summary: 'Login Operator',
        description: 'Login operator menggunakan email dan password.'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'admin@gns.test'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: 'password'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login berhasil'
    )]
    #[OA\Response(
        response: 401,
        description: 'Email atau password salah'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validasi gagal'
    )]
    /**
     * Login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = User::with('roles')->find(Auth::id());

        // Hapus token lama (opsional)
        $user->tokens()->delete();

        $token = $user->createToken('gns-operator')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->values(),
                ],
            ],
        ]);
    }

        #[OA\Post(
        path: '/api/logout',
        operationId: 'logoutOperator',
        tags: ['Authentication'],
        summary: 'Logout',
        security: [['sanctum' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Logout berhasil'
    )]

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

        #[OA\Get(
        path: '/api/me',
        operationId: 'currentUser',
        tags: ['Authentication'],
        summary: 'Profil User Login',
        security: [['sanctum' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Profil user berhasil diambil'
    )]
    /**
     * Profil user login
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values(),
            ],
        ]);
    }
}