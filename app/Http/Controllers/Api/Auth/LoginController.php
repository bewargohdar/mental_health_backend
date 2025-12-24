<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends BaseApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return $this->error('Your account has been deactivated.', 403);
        }

        // Revoke old tokens if requested
        if ($request->boolean('revoke_other_tokens')) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user->load(['roles', 'doctorProfile']),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();
        
        // Revoke current token
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function logoutAll(): JsonResponse
    {
        $user = auth()->user();
        
        // Revoke all tokens
        $user->tokens()->delete();

        return $this->success(null, 'Logged out from all devices');
    }

    public function me(): JsonResponse
    {
        $user = auth()->user()->load(['roles', 'doctorProfile']);
        
        return $this->success($user);
    }
}
