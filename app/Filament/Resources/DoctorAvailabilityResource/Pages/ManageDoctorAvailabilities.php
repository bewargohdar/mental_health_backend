<?php

namespace App\Filament\Resources\DoctorAvailabilityResource\Pages;

use App\Filament\Resources\DoctorAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDoctorAvailabilities extends ManageRecords
{
    protected static string $resource = DoctorAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
