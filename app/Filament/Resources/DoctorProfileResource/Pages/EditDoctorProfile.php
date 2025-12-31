<?php

namespace App\Filament\Resources\DoctorProfileResource\Pages;

use App\Filament\Resources\DoctorProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctorProfile extends EditRecord
{
    protected static string $resource = DoctorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
