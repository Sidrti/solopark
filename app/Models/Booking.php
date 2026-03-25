<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'parking_spot_id',
        'vehicle_id',
        'start_time',
        'end_time',
        'mobile_number',
        'subtotal',
        'service_fee',
        'total_price',
        'status',
        'timezone',
        'is_recurring',
        'recurring_group_id',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(ParkingSpot::class, 'parking_spot_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
