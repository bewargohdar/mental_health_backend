<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends BaseApiController
{
    /**
     * List all verified doctors
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::role('doctor')
            ->whereHas('doctorProfile', function ($q) {
                $q->where('is_verified', true);
            })
            ->with(['doctorProfile', 'doctorProfile.availabilities'])
            ->where('is_active', true);

        // Filter by specialization
        if ($request->has('specialization')) {
            $query->whereHas('doctorProfile', function ($q) use ($request) {
                $q->where('specialization', 'like', '%' . $request->specialization . '%');
            });
        }

        // Filter by language
        if ($request->has('language')) {
            $query->whereHas('doctorProfile', function ($q) use ($request) {
                $q->whereJsonContains('languages', $request->language);
            });
        }

        // Filter by consultation type
        if ($request->has('consultation_type')) {
            $query->whereHas('doctorProfile', function ($q) use ($request) {
                $q->whereJsonContains('consultation_types', $request->consultation_type);
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'experience');
        switch ($sortBy) {
            case 'price_low':
                $query->join('doctor_profiles', 'users.id', '=', 'doctor_profiles.user_id')
                    ->orderBy('doctor_profiles.hourly_rate', 'asc')
                    ->select('users.*');
                break;
            case 'price_high':
                $query->join('doctor_profiles', 'users.id', '=', 'doctor_profiles.user_id')
                    ->orderBy('doctor_profiles.hourly_rate', 'desc')
                    ->select('users.*');
                break;
            case 'experience':
            default:
                $query->join('doctor_profiles', 'users.id', '=', 'doctor_profiles.user_id')
                    ->orderBy('doctor_profiles.experience_years', 'desc')
                    ->select('users.*');
                break;
        }

        $doctors = $query->paginate($request->get('per_page', 10));

        return $this->success($doctors, 'Doctors retrieved successfully');
    }

    /**
     * Get doctor details
     */
    public function show(User $doctor): JsonResponse
    {
        if (!$doctor->hasRole('doctor')) {
            return $this->error('Doctor not found', 404);
        }

        $doctor->load(['doctorProfile', 'doctorProfile.availabilities']);

        if (!$doctor->doctorProfile?->is_verified) {
            return $this->error('Doctor is not verified', 404);
        }

        return $this->success($doctor, 'Doctor details retrieved');
    }

    /**
     * Get doctor specializations list
     */
    public function specializations(): JsonResponse
    {
        $specializations = DoctorProfile::where('is_verified', true)
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->values();

        return $this->success($specializations, 'Specializations retrieved');
    }
}
