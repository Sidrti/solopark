<?php

namespace App\Filament\Resources\ParkingSpots;

use App\Filament\Resources\ParkingSpots\Pages\CreateParkingSpot;
use App\Filament\Resources\ParkingSpots\Pages\EditParkingSpot;
use App\Filament\Resources\ParkingSpots\Pages\ListParkingSpots;
use App\Filament\Resources\ParkingSpots\Schemas\ParkingSpotForm;
use App\Filament\Resources\ParkingSpots\Tables\ParkingSpotsTable;
use App\Models\ParkingSpot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParkingSpotResource extends Resource
{
    protected static ?string $model = ParkingSpot::class;
    protected static ?string $modelLabel = 'Parking Spot';
    protected static ?string $pluralModelLabel = 'Parking Spots';
    protected static ?string $navigationLabel = 'Parking Spots';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    public static function form(Schema $schema): Schema
    {
        return ParkingSpotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParkingSpotsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParkingSpots::route('/'),
            'create' => CreateParkingSpot::route('/create'),
            'edit' => EditParkingSpot::route('/{record}/edit'),
        ];
    }
}
