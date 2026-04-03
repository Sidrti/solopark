<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSpot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'parking_type',
        'price_hourly',
        'price_monthly',
        'is_24_7',
        'features',
        'additional_points',
        'is_active',
    ];

    protected $casts = [
        'is_24_7' => 'boolean',
        'features' => 'array',
        'additional_points' => 'array',
        'price_hourly' => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ParkingSpotAvailability::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ParkingSpotPhoto::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
