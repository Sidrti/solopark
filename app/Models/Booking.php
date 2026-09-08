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
        'spaces_count',
        'start_time',
        'end_time',
        'mobile_number',
        'subtotal',
        'service_fee',
        'tax',
        'gateway_fee',
        'total_price',
        'status',
        'timezone',
        'is_recurring',
        'recurring_group_id',
        'payment_intent_id',
    ];
    
    protected $casts = [
        'spaces_count' => 'integer',
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
