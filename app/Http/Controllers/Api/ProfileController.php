<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends BaseApiController
{
    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return $this->success(
            $user->fresh()->load(['roles', 'doctorProfile']),
            'Profile updated successfully'
        );
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return $this->success(
            $user->fresh()->load(['roles', 'doctorProfile']),
            'Avatar uploaded successfully'
        );
    }

    /**
     * Get user profile
     */
    public function show(): JsonResponse
    {
        $user = auth()->user()->load(['roles', 'doctorProfile']);

        return $this->success($user);
    }

    /**
     * Update user language preference
     */
    public function updateLanguage(string $language): JsonResponse
    {
        $validLanguages = ['en', 'ar', 'ku'];

        if (!in_array($language, $validLanguages)) {
            return $this->error('Invalid language. Supported: en, ar, ku', 400);
        }

        $user = auth()->user();
        
        $privacySettings = $user->privacy_settings ?? [];
        $privacySettings['language'] = $language;
        
        $user->update(['privacy_settings' => $privacySettings]);

        return $this->success(['language' => $language], 'Language updated successfully');
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(): JsonResponse
    {
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return $this->success(null, 'Avatar deleted successfully');
    }
}
