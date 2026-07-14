<?php

namespace App\Filament\Resources\ParkingSpots\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;

class ParkingSpotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->label('Owner'),

                Select::make('parking_type')
                    ->options([
                        'Driveway' => 'Driveway',
                        'Garage' => 'Garage',
                        'Uncovered Lot' => 'Uncovered Lot',
                        'Covered Lot' => 'Covered Lot',
                        'Backyard' => 'Backyard',
                    ])
                    ->required()
                    ->label('Parking Type'),

                TextInput::make('price_hourly')
                    ->numeric()
                    ->prefix('CA$')
                    ->minValue(4)
                    ->label('Hourly Price'),

                TextInput::make('price_daily')
                    ->numeric()
                    ->prefix('CA$')
                    ->minValue(1)
                    ->label('Daily Price (per hour)'),

                TextInput::make('price_monthly')
                    ->numeric()
                    ->prefix('CA$')
                    ->label('Monthly Price'),

                TextInput::make('address')
                    ->required()
                    ->maxLength(255),

                TextInput::make('city')
                    ->maxLength(255),

                TextInput::make('state')
                    ->maxLength(255),

                TextInput::make('country')
                    ->maxLength(255),

                TextInput::make('latitude')
                    ->numeric()
                    ->step('any'),

                TextInput::make('longitude')
                    ->numeric()
                    ->step('any'),

                Toggle::make('is_24_7')
                    ->label('24/7 Availability'),

                Toggle::make('is_active')
                    ->label('Listing Active'),

                TagsInput::make('features')
                    ->label('Features'),

                TagsInput::make('additional_points')
                    ->label('Additional Points'),
            ]);
    }
}
