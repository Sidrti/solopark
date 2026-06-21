<?php

namespace App\Filament\Resources\ParkingSpots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
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
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_number')
                    ->label('Contact Number'),

                TextColumn::make('parking_type')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('price_hourly')
                    ->label('Hourly Price')
                    ->formatStateUsing(fn($state) => 'CA$ ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('price_daily')
                    ->label('Daily Price/hr')
                    ->formatStateUsing(fn ($state) => $state ? 'CA$ ' . number_format($state, 2) : 'N/A')
                    ->sortable(),
                TextColumn::make('price_monthly')
                    ->label('Monthly Price')
                    ->formatStateUsing(fn($state) => $state ? 'CA$ ' . number_format($state, 2) : 'N/A')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
