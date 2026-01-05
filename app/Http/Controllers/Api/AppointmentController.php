<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\User;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppointmentController extends BaseApiController
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = $user->isDoctor()
            ? Appointment::forDoctor($user->id)
            : Appointment::forPatient($user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('scheduled_at', '>=', Carbon::parse($request->from_date));
        }
        if ($request->has('to_date')) {
            $query->where('scheduled_at', '<=', Carbon::parse($request->to_date));
        }

        $appointments = $query
            ->with(['patient:id,name,email,avatar', 'doctor:id,name,email,avatar', 'doctor.doctorProfile'])
            ->orderBy('scheduled_at', 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->success($appointments);
    }

    public function store(AppointmentRequest $request): JsonResponse
    {
        // Check if slot is available
        $doctor = User::findOrFail($request->doctor_id);
        
        if (!$doctor->isDoctor() || !$doctor->isVerifiedDoctor()) {
            return $this->error('Invalid doctor selected.', 422);
        }

        $scheduledAt = Carbon::parse($request->scheduled_at);

        // Check availability
        $availability = DoctorAvailability::where('doctor_id', $request->doctor_id)
            ->where('day_of_week', $scheduledAt->dayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $scheduledAt->format('H:i:s'))
            ->whereTime('end_time', '>', $scheduledAt->format('H:i:s'))
            ->first();

        if (!$availability) {
            return $this->error('Doctor is not available at this time.', 422);
        }

        // Check if user already has an active appointment with this doctor
        $existingAppointment = Appointment::where('patient_id', auth()->id())
            ->where('doctor_id', $request->doctor_id)
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED])
            ->first();

        if ($existingAppointment) {
            return $this->error('You already have an active appointment with this doctor. Please complete or cancel it before booking a new one.', 422);
        }

        // Check for conflicts (same time slot already booked)
        $conflict = Appointment::where('doctor_id', $request->doctor_id)
            ->where('scheduled_at', $scheduledAt)
            ->whereNotIn('status', [AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED])
            ->exists();

        if ($conflict) {
            return $this->error('This time slot is already booked.', 422);
        }

        $appointment = Appointment::create([
            'patient_id' => auth()->id(),
            'doctor_id' => $request->doctor_id,
            'scheduled_at' => $scheduledAt,
            'duration' => $availability->slot_duration,
            'patient_notes' => $request->patient_notes,
            'status' => AppointmentStatus::PENDING,
        ]);

        $appointment->load(['patient:id,name,email', 'doctor:id,name,email']);

        return $this->created($appointment, 'Appointment request submitted.');
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient:id,name,email,avatar', 'doctor:id,name,email,avatar', 'doctor.doctorProfile']);

        return $this->success($appointment);
    }

    public function confirm(Appointment $appointment): JsonResponse
    {
        $this->authorize('confirm', $appointment);

        if ($appointment->status !== AppointmentStatus::PENDING) {
            return $this->error('Only pending appointments can be confirmed.', 422);
        }

        $appointment->update(['status' => AppointmentStatus::CONFIRMED]);

        // Notify patient
        $appointment->patient->notify(new AppointmentConfirmed($appointment));

        return $this->success($appointment, 'Appointment confirmed.');
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('cancel', $appointment);

        if (!$appointment->canBeCancelled()) {
            return $this->error('This appointment cannot be cancelled.', 422);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
            'cancellation_reason' => $request->reason,
        ]);

        return $this->success($appointment, 'Appointment cancelled.');
    }

    public function complete(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('complete', $appointment);

        if ($appointment->status !== AppointmentStatus::CONFIRMED) {
            return $this->error('Only confirmed appointments can be completed.', 422);
        }

        $appointment->update([
            'status' => AppointmentStatus::COMPLETED,
            'completed_at' => now(),
            'notes' => $request->notes, // Encrypted in model
        ]);

        return $this->success($appointment, 'Appointment completed.');
    }

    public function doctorAvailability(User $doctor): JsonResponse
    {
        if (!$doctor->isDoctor()) {
            return $this->error('User is not a doctor.', 404);
        }

        $availabilities = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->get();

        return $this->success($availabilities);
    }

    public function availableSlots(Request $request, User $doctor): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $date = Carbon::parse($request->date);

        if (!$doctor->isDoctor()) {
            return $this->error('User is not a doctor.', 404);
        }

        // First check for specific date availability, then fall back to recurring day_of_week
        $availability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereDate('specific_date', $date->toDateString())
                    ->orWhere(function ($q) use ($date) {
                        $q->whereNull('specific_date')
                          ->where('day_of_week', $date->dayOfWeek);
                    });
            })
            ->orderByRaw('specific_date IS NULL') // Prefer specific date over recurring
            ->first();

        if (!$availability) {
            return $this->success(['slots' => []], 'Doctor not available on this day.');
        }

        // Get booked slots
        $bookedSlots = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $date)
            ->whereNotIn('status', [AppointmentStatus::CANCELLED])
            ->pluck('scheduled_at')
            ->map(fn($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        // Generate available slots
        $slots = [];
        // Extract time portion from start_time/end_time (they may be stored as full datetime)
        $startTimeStr = $availability->start_time instanceof \DateTime 
            ? $availability->start_time->format('H:i:s') 
            : $availability->start_time;
        $endTimeStr = $availability->end_time instanceof \DateTime 
            ? $availability->end_time->format('H:i:s') 
            : $availability->end_time;
        
        $startTime = Carbon::parse($date->toDateString() . ' ' . $startTimeStr);
        $endTime = Carbon::parse($date->toDateString() . ' ' . $endTimeStr);

        while ($startTime < $endTime) {
            $slotTime = $startTime->format('H:i');
            $isBooked = in_array($slotTime, $bookedSlots);
            $slots[] = [
                'time' => $slotTime,
                'datetime' => $startTime->toDateTimeString(),
                'available' => !$isBooked,
            ];
            $startTime->addMinutes($availability->slot_duration);
        }

        return $this->success(['slots' => $slots]);
    }

}
