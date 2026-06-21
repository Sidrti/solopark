<?php

namespace App\Filament\Resources\ParkingSpots\Pages;

use App\Filament\Resources\ParkingSpots\ParkingSpotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParkingSpot extends EditRecord
{
    protected static string $resource = ParkingSpotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
