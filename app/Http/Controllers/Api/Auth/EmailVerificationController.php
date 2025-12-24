<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends BaseApiController
{
    public function notice(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->hasVerifiedEmail()) {
            return $this->success(['verified' => true], 'Email already verified.');
        }

        return $this->success(['verified' => false], 'Email verification required.');
    }

    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(['verified' => true], 'Email already verified.');
        }

        $request->fulfill();

        return $this->success(['verified' => true], 'Email verified successfully.');
    }

    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(['verified' => true], 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success(null, 'Verification link sent.');
    }
}
