<?php

namespace App\Filament\Resources\WellnessTipResource\Pages;

use App\Filament\Resources\WellnessTipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWellnessTip extends EditRecord
{
    protected static string $resource = WellnessTipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
