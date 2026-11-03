<?php

namespace App\Services;

use App\Models\ParkingSpot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DailyBookingQuote
{
    public function quote(ParkingSpot $spot, array $input): array
    {
        $data = Validator::make($input, [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'timezone' => 'required|timezone',
            'days' => ['required', 'regex:/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun)(,(Mon|Tue|Wed|Thu|Fri|Sat|Sun))*$/'],
            'spaces_count' => 'required|integer|min:1',
        ])->validate();
        $current = Carbon::parse($data['startDate'], $data['timezone']);
        $last = Carbon::parse($data['endDate'], $data['timezone']);
        if ($current->diffInDays($last) >= 1000) {
            throw ValidationException::withMessages(['endDate' => 'Choose a date range shorter than 1,000 days.']);
        }
        $selected = explode(',', $data['days']);
        $minutes = fn ($time) => ((int) substr($time, 0, 2)) * 60 + (int) substr($time, 3, 2);
        $start = $minutes($data['startTime']);
        $duration = ($minutes($data['endTime']) - $start + 1440) % 1440 ?: 1440;
        $count = 0;
        while ($current->lte($last)) {
            if (in_array($current->format('D'), $selected)) {
                if (!$spot->is_24_7) {
                    $allowed = $spot->availabilities->where('day_of_week', $current->format('D'))->contains(function ($window) use ($minutes, $start, $duration) {
                        $windowStart = $minutes($window->start_time);
                        $windowLength = ($minutes($window->end_time) - $windowStart + 1440) % 1440 ?: 1440;
                        $offset = ($start - $windowStart + 1440) % 1440;
                        return $offset + $duration <= $windowLength;
                    });
                    if (!$allowed) {
                        throw ValidationException::withMessages(['startTime' => 'Select parking hours within the host availability on every booked day.']);
                    }
                }
                $count++;
            }
            $current->addDay();
        }
        if (!$count) throw ValidationException::withMessages(['days' => 'No selected days fall in this date range.']);
        $daily = (float) ($spot->price_daily ?? $spot->price_hourly * 12);
        $monthly = $spot->price_monthly_limited !== null ? (float) $spot->price_monthly_limited : ($spot->price_monthly !== null ? (float) $spot->price_monthly : null);
        $base = DailyPricing::base($count, $daily, $monthly, (int) ceil(max(0, $duration - 720) / 60));
        return DailyPricing::fees($base * $data['spaces_count']);
    }
}
