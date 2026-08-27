<?php

namespace App\Filament\Resources\ParkingSpots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ParkingSpotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->description(fn ($record) => $record->user?->email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_number')
                    ->label('Contact')
                    ->searchable(),

                TextColumn::make('parking_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price_hourly')
                    ->label('Hourly')
                    ->formatStateUsing(fn ($state) => $state ? 'CA$ ' . number_format($state, 2) : '—')
                    ->sortable(),
                TextColumn::make('price_daily')
                    ->label('Daily/hr')
                    ->formatStateUsing(fn ($state) => $state ? 'CA$ ' . number_format($state, 2) : '—')
                    ->sortable(),
                TextColumn::make('price_monthly')
                    ->label('Monthly')
                    ->formatStateUsing(fn ($state) => $state ? 'CA$ ' . number_format($state, 2) : '—')
                    ->sortable(),
                IconColumn::make('is_24_7')
                    ->boolean()
                    ->label('24/7')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                IconColumn::make('dummy')
                    ->boolean()
                    ->label('Booked')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Owner'),
                SelectFilter::make('parking_type')
                    ->options([
                        'Driveway' => 'Driveway',
                        'Garage' => 'Garage',
                        'Uncovered Lot' => 'Uncovered Lot',
                        'Covered Lot' => 'Covered Lot',
                        'Backyard' => 'Backyard',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                TernaryFilter::make('is_24_7')
                    ->label('24/7 Availability'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
