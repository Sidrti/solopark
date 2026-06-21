<?php

namespace App\Filament\Resources\ParkingSpots\Pages;

use App\Filament\Resources\ParkingSpots\ParkingSpotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParkingSpots extends ListRecords
{
    protected static string $resource = ParkingSpotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
