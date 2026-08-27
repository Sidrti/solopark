<?php

namespace App\Filament\Resources\ParkingSpots\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ParkingSpotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slot Ownership & Status')
                    ->description('Assign this parking slot to a user and set its live status.')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->required()
                            ->label('Owner / User')
                            ->helperText('Select the user who owns this slot. Admin can add spots for any registered user.'),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Listing Active')
                                    ->default(true)
                                    ->helperText('When active, this slot is discoverable and available for bookings.'),

                                Toggle::make('dummy')
                                    ->label('Mark as Booked / Unavailable')
                                    ->default(false)
                                    ->helperText('Enable to display this spot as occupied/booked badge on search results.'),
                            ]),
                    ]),

                Section::make('Basic Details')
                    ->description('General information and contact for the parking spot.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Spot Title')
                            ->placeholder('e.g. Secure Downtown Driveway')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('parking_type')
                            ->label('Parking Type')
                            ->options([
                                'Driveway' => 'Driveway',
                                'Garage' => 'Garage',
                                'Uncovered Lot' => 'Uncovered Lot',
                                'Covered Lot' => 'Covered Lot',
                                'Backyard' => 'Backyard',
                            ])
                            ->required(),

                        TextInput::make('contact_number')
                            ->label('Contact Phone Number')
                            ->tel()
                            ->required()
                            ->placeholder('(555) 555-5555')
                            ->helperText('Phone number for driver contact & notifications.'),
                    ])
                    ->columns(2),

                Section::make('Pricing Configuration')
                    ->description('Set hourly, daily, and monthly rates for this parking slot.')
                    ->schema([
                        TextInput::make('price_hourly')
                            ->numeric()
                            ->prefix('CA$')
                            ->minValue(4)
                            ->label('Hourly Price')
                            ->helperText('Hourly rate for one-time bookings (Min CA$ 4.00)'),

                        TextInput::make('price_daily')
                            ->numeric()
                            ->prefix('CA$')
                            ->minValue(1)
                            ->label('Daily Price (per hour)')
                            ->helperText('Hourly rate applied during recurring daily schedule bookings'),

                        TextInput::make('price_monthly')
                            ->numeric()
                            ->prefix('CA$')
                            ->minValue(0)
                            ->label('Monthly Price')
                            ->helperText('Flat monthly subscription price'),
                    ])
                    ->columns(3),

                Section::make('Location Details')
                    ->description('Enter address via Google Autocomplete to automatically populate city, province, country, and GPS coordinates.')
                    ->schema([
                        TextInput::make('address')
                            ->label('Street Address')
                            ->placeholder('Start typing address with Google Autocomplete...')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->extraInputAttributes([
                                'id' => 'admin-spot-address-input',
                                'autocomplete' => 'off',
                                'x-on:keydown.enter.prevent' => '',
                                'x-data' => '{}',
                                'x-init' => "
                                    const initAddressAutocomplete = () => {
                                        if (!window.google || !window.google.maps || !window.google.maps.places) {
                                            setTimeout(initAddressAutocomplete, 300);
                                            return;
                                        }
                                        const autocomplete = new window.google.maps.places.Autocomplete(\$el, {
                                            types: ['geocode'],
                                        });
                                        autocomplete.addListener('place_changed', () => {
                                            const place = autocomplete.getPlace();
                                            if (!place || !place.geometry) return;

                                            const formattedAddress = place.formatted_address || place.name || '';
                                            const lat = place.geometry.location.lat();
                                            const lng = place.geometry.location.lng();

                                            let city = '';
                                            let state = '';
                                            let country = '';

                                            if (place.address_components) {
                                                for (const component of place.address_components) {
                                                    const types = component.types;
                                                    if (types.includes('locality') || types.includes('postal_town')) {
                                                        city = component.long_name;
                                                    } else if (types.includes('administrative_area_level_1')) {
                                                        state = component.short_name;
                                                    } else if (types.includes('country')) {
                                                        country = component.long_name;
                                                    }
                                                }
                                            }

                                            \$wire.set('data.address', formattedAddress);
                                            \$wire.set('data.latitude', lat);
                                            \$wire.set('data.longitude', lng);
                                            \$wire.set('data.city', city);
                                            \$wire.set('data.state', state);
                                            \$wire.set('data.country', country);
                                        });
                                    };
                                    initAddressAutocomplete();
                                ",
                            ]),

                        TextInput::make('city')
                            ->label('City')
                            ->placeholder('Auto-populated')
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('Province / State')
                            ->placeholder('Auto-populated')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label('Country')
                            ->default('Canada')
                            ->placeholder('Auto-populated')
                            ->maxLength(255),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step('any')
                            ->placeholder('Auto-populated (e.g. 43.6532)'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step('any')
                            ->placeholder('Auto-populated (e.g. -79.3832)'),
                    ])
                    ->columns(3),

                Section::make('Availability & Time Slots')
                    ->description('Configure whether this slot is open 24/7 or only during specific days and operating hours.')
                    ->schema([
                        Toggle::make('is_24_7')
                            ->label('Open 24/7 (Always Available)')
                            ->helperText('Enable if the parking spot is accessible around the clock. Disable to define custom daily timeslots.')
                            ->live()
                            ->default(false),

                        Repeater::make('availabilities')
                            ->relationship('availabilities')
                            ->label('Daily Operating Time Slots')
                            ->schema([
                                Select::make('day_of_week')
                                    ->label('Day')
                                    ->options([
                                        'Mon' => 'Monday',
                                        'Tue' => 'Tuesday',
                                        'Wed' => 'Wednesday',
                                        'Thu' => 'Thursday',
                                        'Fri' => 'Friday',
                                        'Sat' => 'Saturday',
                                        'Sun' => 'Sunday',
                                    ])
                                    ->required(),

                                TimePicker::make('start_time')
                                    ->label('Available From')
                                    ->seconds(false)
                                    ->required(),

                                TimePicker::make('end_time')
                                    ->label('Available To')
                                    ->seconds(false)
                                    ->required(),

                                Toggle::make('is_available')
                                    ->label('Available')
                                    ->default(true),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Day / Time Slot')
                            ->collapsible()
                            ->hidden(fn ($get) => (bool) $get('is_24_7')),
                    ]),

                Section::make('Photos')
                    ->description('Upload and manage photos for this parking spot.')
                    ->schema([
                        Repeater::make('photos')
                            ->relationship('photos')
                            ->label('Spot Photos')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Photo')
                                    ->disk('public')
                                    ->directory('parking_spots')
                                    ->image()
                                    ->required(),
                            ])
                            ->grid(3)
                            ->addActionLabel('Add Photo')
                            ->collapsible(),
                    ]),

                Section::make('Features & Additional Information')
                    ->description('Amenities, security features, and special parking instructions.')
                    ->schema([
                        TagsInput::make('features')
                            ->label('Features / Amenities')
                            ->placeholder('Type and press enter (e.g. CCTV, EV Charging, Paved, Gated)'),

                        TagsInput::make('additional_points')
                            ->label('Additional Guidelines / Notes')
                            ->placeholder('Type and press enter (e.g. Park on right side, Ring doorbell if needed)'),
                    ]),
            ]);
    }
}
