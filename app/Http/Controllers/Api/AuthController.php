<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Ensure user_type is returned as string value for Flutter compatibility
        $userData = $user->toArray();
        if (isset($userData['user_type']) && $userData['user_type'] instanceof \App\Enums\UserType) {
            $userData['user_type'] = $userData['user_type']->value;
        }

        return response()->json([
            'token' => $token,
            'user' => $userData,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user_type is returned as string value for Flutter compatibility
        $userData = $user->toArray();
        if (isset($userData['user_type']) && $userData['user_type'] instanceof \App\Enums\UserType) {
            $userData['user_type'] = $userData['user_type']->value;
        }
        
        return response()->json($userData);
    }
}
