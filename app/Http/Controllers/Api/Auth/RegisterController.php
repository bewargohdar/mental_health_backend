<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;

class RegisterController extends BaseApiController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(60),
            ]);

            // Assign role
            $role = $request->role ?? 'user';
            $user->assignRole($role);

            // If registering as doctor, create doctor profile
            if ($role === 'doctor') {
                DoctorProfile::create([
                    'user_id' => $user->id,
                    'specialization' => $request->specialization,
                    'license_number' => $request->license_number,
                    'bio' => $request->bio,
                    'experience_years' => $request->experience_years ?? 0,
                ]);
            }

            event(new Registered($user));

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->created([
                'user' => $user->load('roles'),
                'token' => $token,
            ], 'Registration successful. Please verify your email.');
        });
    }

    public function registerDoctor(RegisterRequest $request): JsonResponse
    {
        $request->merge(['role' => 'doctor']);
        return $this->register($request);
    }
}
