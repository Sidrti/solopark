<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->formatStateUsing(fn ($state) => $state)
                    ->sortable(),
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('mobile_number')
                    ->label('Phone Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('spot.title')->searchable()->sortable(),
                TextColumn::make('total_price')
                    ->formatStateUsing(fn ($state) => 'CA$ ' . number_format($state, 0))
                    ->sortable(),
                TextColumn::make('manual_payout_estimate')
                    ->label('Host payout estimate')
                    ->state(fn ($record) => (float) $record->subtotal * (1 - config('parking.host_deduction_percentage') / 100))
                    ->formatStateUsing(fn ($state) => 'CA$ ' . number_format($state, 2))
                    ->tooltip('Manual payout estimate after host deduction; no automatic payout.'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
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
