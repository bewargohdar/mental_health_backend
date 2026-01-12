<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\DoctorProfile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing doctor profile data
        if ($this->record->doctorProfile) {
            $data['doctorProfile'] = $this->record->doctorProfile->toArray();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->handleEmailVerification();
        $this->handleDoctorProfile();
    }

    protected function handleEmailVerification(): void
    {
        $record = $this->record;
        $data = $this->data;

        if (isset($data['email_verified'])) {
            if ($data['email_verified'] && !$record->email_verified_at) {
                $record->update(['email_verified_at' => now()]);
            } elseif (!$data['email_verified'] && $record->email_verified_at) {
                $record->update(['email_verified_at' => null]);
            }
        }
    }

    protected function handleDoctorProfile(): void
    {
        $record = $this->record;
        $data = $this->data;

        $hasDoctoRole = $record->hasRole('doctor');

        if ($hasDoctoRole && isset($data['doctorProfile'])) {
            $profileData = array_filter($data['doctorProfile'], fn ($value) => $value !== null && $value !== '');

            if (!empty($profileData)) {
                // Update or create doctor profile
                $record->doctorProfile()->updateOrCreate(
                    ['user_id' => $record->id],
                    $profileData
                );
            }
        } elseif (!$hasDoctoRole && $record->doctorProfile) {
            // Optionally remove doctor profile when role is removed
            // Uncomment this line if you want to delete the profile when doctor role is removed:
            // $record->doctorProfile()->delete();
        }
    }
}
