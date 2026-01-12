<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\DoctorProfile;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->handleDoctorProfile();
    }

    protected function handleDoctorProfile(): void
    {
        $record = $this->record;
        $data = $this->data;

        // Check if user has doctor role
        $doctorRole = Role::where('name', 'doctor')->first();
        $hasDoctoRole = $record->hasRole('doctor');

        if ($hasDoctoRole && isset($data['doctorProfile'])) {
            // Create doctor profile
            $profileData = array_filter($data['doctorProfile'], fn ($value) => $value !== null && $value !== '');
            
            if (!empty($profileData)) {
                $record->doctorProfile()->create($profileData);
            }
        }
    }
}
