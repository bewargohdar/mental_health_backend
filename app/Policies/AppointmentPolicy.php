<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->patient_id 
            || $user->id === $appointment->doctor_id 
            || $user->isAdmin();
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->doctor_id;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->patient_id 
            || $user->id === $appointment->doctor_id 
            || $user->isAdmin();
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->doctor_id;
    }
}
