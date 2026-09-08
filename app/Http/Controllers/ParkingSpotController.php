<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkingSpot;
use App\Models\ParkingSpotAvailability;
use App\Models\ParkingSpotPhoto;
use Illuminate\Support\Facades\Auth;

class ParkingSpotController extends Controller
{
    public function userListings()
    {
        $spots = ParkingSpot::where('user_id', Auth::id())
            ->with(['photos', 'bookings.user'])
            ->latest()
            ->get()
            ->map(function ($spot) {
                $photos = $spot->relationLoaded('photos') ? $spot->getRelation('photos') : collect([]);
                $firstPhoto = $photos->first();

                $bookings = $spot->bookings->map(
                    function ($booking) {
                        return [
                            'id' => $booking->id,
                            'customer' => $booking->user->name,
                            'email' => $booking->user->email,
                            'start_time' => $booking->start_time,
                            'end_time' => $booking->end_time,
                            'subtotal' => $booking->subtotal,
                            'service_fee' => $booking->service_fee,
                            'tax' => $booking->tax,
                            'gateway_fee' => $booking->gateway_fee,
                            'total_price' => round($booking->total_price, 0),
                            'spaces_count' => (int) ($booking->spaces_count ?? 1),
                            'status' => $booking->status,
                        ];
                    }
                );

                return [
                    'id' => $spot->id,
                    'title' => $spot->title,
                    'address' => $spot->address . ($spot->city ? ', ' . $spot->city : ''),
                    'price_hourly' => $spot->price_hourly,
                    'price_monthly' => $spot->price_monthly,
                    'price_daily' => $spot->price_daily,
                    'is_active' => $spot->is_active,
                    'is_approved' => (bool) $spot->is_approved,
                    'total_spaces' => (int) ($spot->total_spaces ?? 1),
                    'service_fee_percentage' => (float) ($spot->service_fee_percentage ?? 10.00),
                    'service_fee_monthly_percentage' => (float) ($spot->service_fee_monthly_percentage ?? 30.00),
                    'bookings' => $bookings,
                    'image' => $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=400',
                ];
            });

        return \Inertia\Inertia::render('MyListings', [
            'spots' => $spots
        ]);
    }

    public function index(Request $request)
    {
        if (empty($request->query())) {
            return redirect()->route('home');
        }
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $start = $request->input('start');
        $end = $request->input('end');
        $locationStr = $request->input('location');

        $query = ParkingSpot::query()->where('is_approved', true)->with('photos');

        $latitude = $lat ?: 43.6532; // Default to Toronto
        $longitude = $lng ?: -79.3832;

        if ($lat && $lng) {
            $query->select('*')
                ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                ->orderByRaw('CASE WHEN is_active = 1 AND dummy = 0 THEN 0 ELSE 1 END ASC')
                ->orderBy('distance');
        } else {
            $query->orderByRaw('CASE WHEN is_active = 1 AND dummy = 0 THEN 0 ELSE 1 END ASC')
                ->latest();
        }

        $timezone = $request->input('timezone', config('app.timezone'));
        $searchType = $request->input('type', 'one-time');

        if ($searchType === 'one-time') {
            $query->whereNotNull('price_hourly');
            if ($start && $end) {
                try {
                $startDt = \Carbon\Carbon::parse($start, $timezone);
                $endDt = \Carbon\Carbon::parse($end, $timezone);

                $dayOfWeek = $startDt->copy()->setTimezone($timezone)->format('D');
                $startTimeStr = $startDt->copy()->setTimezone($timezone)->format('H:i:s');
                $endTimeStr = $endDt->copy()->setTimezone($timezone)->format('H:i:s');

                $query->whereHas('availabilities', function ($q) use ($dayOfWeek, $startTimeStr, $endTimeStr) {
                    $q->where('day_of_week', $dayOfWeek)
                        ->where('start_time', '<=', $startTimeStr)
                        ->where('end_time', '>=', $endTimeStr);
                });

                // For bookings, we MUST use UTC comparison
                $startUtc = $startDt->copy()->setTimezone('UTC')->toDateTimeString();
                $endUtc = $endDt->copy()->setTimezone('UTC')->toDateTimeString();

                // Filter out spots that don't have enough capacity
                $query->whereRaw('(parking_spots.total_spaces - (
                    SELECT COALESCE(SUM(spaces_count), 0)
                    FROM bookings
                    WHERE bookings.parking_spot_id = parking_spots.id
                      AND bookings.start_time < ?
                      AND bookings.end_time > ?
                      AND bookings.status != "cancelled"
                )) >= ?', [$endUtc, $startUtc, 1]);
            } catch (\Exception $e) {
                // Ignore parsing errors
                }
            }
        } elseif ($searchType === 'recurring') {
            $query->whereNotNull('price_daily');
            
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $startTime = $request->input('startTime');
            $endTime = $request->input('endTime');
            $days = explode(',', $request->input('days', ''));

            if ($startDate && $endDate && $startTime && $endTime && !empty($days)) {
                try {
                    $startDt = \Carbon\Carbon::parse($startDate . ' ' . $startTime, $timezone);
                    $endDt = \Carbon\Carbon::parse($endDate . ' ' . $endTime, $timezone);

                    // For recurring, the spot must be available on ALL requested days within the range
                    // and have NO overlapping bookings on ANY of those days
                    $query->where(function ($q) use ($days, $startTime, $endTime) {
                        $q->where('is_24_7', true)
                            ->orWhere(function ($availQ) use ($days, $startTime, $endTime) {
                                foreach ($days as $day) {
                                    $availQ->whereHas(
                                        'availabilities',
                                        function ($subQ) use ($day, $startTime, $endTime) {
                                            $subQ->where('day_of_week', $day);
                                            if ($startTime <= $endTime) {
                                                $subQ->where('start_time', '<=', $startTime)
                                                    ->where('end_time', '>=', $endTime);
                                            } else {
                                                // Overnight: slot availability covering the night schedule
                                                $subQ->where('start_time', '<=', $startTime)
                                                    ->where('end_time', '>=', $endTime);
                                            }
                                        }
                                    );
                                }
                            });
                    });

                    // Check for overlaps on each requested day in the range
                    $current = \Carbon\Carbon::parse($startDate, $timezone);
                    $endRange = \Carbon\Carbon::parse($endDate, $timezone);

                    while ($current->lte($endRange)) {
                        if (in_array($current->format('D'), $days)) {
                            $startCarbon = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $startTime, $timezone);
                            $endCarbon = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $endTime, $timezone);
                            if ($endCarbon->lte($startCarbon)) {
                                $endCarbon->addDay();
                            }
                            $dayStartUtc = $startCarbon->setTimezone('UTC')->toDateTimeString();
                            $dayEndUtc = $endCarbon->setTimezone('UTC')->toDateTimeString();

                            $query->whereRaw('(parking_spots.total_spaces - (
                                SELECT COALESCE(SUM(spaces_count), 0)
                                FROM bookings
                                WHERE bookings.parking_spot_id = parking_spots.id
                                  AND bookings.start_time < ?
                                  AND bookings.end_time > ?
                                  AND bookings.status != "cancelled"
                            )) >= ?', [$dayEndUtc, $dayStartUtc, 1]);
                        }
                        $current->addDay();
                    }
                } catch (\Exception $e) {
                    // Ignore parsing errors
                }
            }
        } elseif ($searchType === 'monthly') {
            $query->whereNotNull('price_monthly');

            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            if ($startDate && $endDate) {
                // For monthly, we simply check if there are any overlapping bookings (regardless of one-time/recurring/monthly)
                // because a monthly booking usually implies 24/7 reservation of the space.
                $startUtc = \Carbon\Carbon::parse($startDate, $timezone)->setTimezone('UTC')->toDateTimeString();
                $endUtc = \Carbon\Carbon::parse($endDate, $timezone)->setTimezone('UTC')->toDateTimeString();

                $query->whereRaw('(parking_spots.total_spaces - (
                    SELECT COALESCE(SUM(spaces_count), 0)
                    FROM bookings
                    WHERE bookings.parking_spot_id = parking_spots.id
                      AND bookings.start_time < ?
                      AND bookings.end_time > ?
                      AND bookings.status != "cancelled"
                )) >= ?', [$endUtc, $startUtc, 1]);
            }
        }

        $spots = $query->get()->map(function ($spot) use ($request, $searchType, $timezone) {
            $photos = $spot->relationLoaded('photos') ? $spot->getRelation('photos') : collect([]);
            $firstPhoto = $photos->first();

            $distKm = $spot->distance ?? 0;
            $speedKmh = 5; // Average walking speed in km/h
            $walkMinutes = $speedKmh > 0 ? round(($distKm / $speedKmh) * 60) : 0;

            // Calculate Final Price Including Fees and Taxes
            $baseCost = 0;
            if ($searchType === 'monthly') {
                $startDate = $request->input('startDate');
                $endDate = $request->input('endDate');
                $months = 1;
                if ($startDate && $endDate) {
                    $start = \Carbon\Carbon::parse($startDate, $timezone);
                    $end = \Carbon\Carbon::parse($endDate, $timezone);
                    $diffDays = $start->diffInDays($end);
                    $months = max(1, ceil($diffDays / 30));
                }
                $baseCost = ($spot->price_monthly ?? $spot->price_hourly) * $months;
            } else if ($searchType === 'one-time') {
                $start = $request->input('start');
                $end = $request->input('end');
                $durationUnits = 2; // Default 1 hour
                if ($start && $end) {
                    $startDt = \Carbon\Carbon::parse($start, $timezone);
                    $endDt = \Carbon\Carbon::parse($end, $timezone);
                    $diffMins = $startDt->diffInMinutes($endDt);
                    $durationUnits = max(1, ceil($diffMins / 30));
                }
                $baseCost = ($spot->price_hourly / 2) * $durationUnits;
            } else {
                // Recurring
                $startDate = $request->input('startDate');
                $endDate = $request->input('endDate');
                $startTime = $request->input('startTime');
                $endTime = $request->input('endTime');
                $days = explode(',', $request->input('days', ''));

                $durationUnits = 0;
                if ($startDate && $endDate && $startTime && $endTime && !empty($days)) {
                    $startRange = \Carbon\Carbon::parse($startDate, $timezone);
                    $endRange = \Carbon\Carbon::parse($endDate, $timezone);

                    $sParts = explode(':', $startTime);
                    $eParts = explode(':', $endTime);
                    $dailyMinutes = ($eParts[0] * 60 + $eParts[1]) - ($sParts[0] * 60 + $sParts[1]);
                    if ($dailyMinutes <= 0) {
                        $dailyMinutes += 24 * 60;
                    }
                    $dailyUnits = max(0, ceil($dailyMinutes / 30));

                    $current = $startRange->copy();
                    while ($current->lte($endRange)) {
                        if (in_array($current->format('D'), $days)) {
                            $durationUnits += $dailyUnits;
                        }
                        $current->addDay();
                    }
                }
                if ($durationUnits == 0)
                    $durationUnits = 2;
                $baseCost = (($spot->price_daily ?? $spot->price_hourly) / 2) * $durationUnits;
            }

            $serviceRate = ($searchType === 'monthly')
                ? (float) (($spot->service_fee_monthly_percentage ?? 30.00) / 100)
                : (float) (($spot->service_fee_percentage ?? 10.00) / 100);
            $finalPrice = $baseCost * (1 + $serviceRate) * 1.13 * 1.03;

            // Deterministic hash-based offset to obfuscate exact lat/lng on search map
            $seed = crc32($spot->id);
            mt_srand($seed);
            $latOffset = (mt_rand(-120, 120) / 100000);
            $lngOffset = (mt_rand(-120, 120) / 100000);
            mt_srand(); // reset seed randomizer

            $approxLat = $spot->latitude + $latOffset;
            $approxLng = $spot->longitude + $lngOffset;

            // Obfuscate exact street address (e.g. "123 Matheson Blvd" -> "Near Matheson Blvd")
            $approxAddress = preg_replace('/^\d+\s+/', 'Near ', $spot->address);
            $displayAddress = $approxAddress . ($spot->city ? ', ' . $spot->city : '');

            return [
                'id' => $spot->id,
                'title' => $spot->title,
                'address' => $displayAddress,
                'rating' => 4.5,
                'reviews' => 10,
                'walk' => $walkMinutes . ' min',
                'dist' => isset($spot->distance) ? number_format($spot->distance, 1) : '0.0',
                'price' => round($finalPrice, 0),
                'badge' => $spot->dummy ? 'Booked' : ($searchType === 'monthly' ? 'Monthly' : null),
                'image' => $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=400',
                'lat' => $approxLat,
                'lng' => $approxLng,
                'dummy' => (bool) $spot->dummy,
                'is_active' => (bool) $spot->is_active,
                'is_approved' => (bool) $spot->is_approved,
                'total_spaces' => (int) ($spot->total_spaces ?? 1),
                'service_fee_percentage' => (float) ($spot->service_fee_percentage ?? 10.00),
                'service_fee_monthly_percentage' => (float) ($spot->service_fee_monthly_percentage ?? 30.00),
                'price_daily' => $spot->price_daily,
                'price_hourly' => $spot->price_hourly,
            ];
        });

        // Ensure all available spots appear first, unavailable listings afterward
        $spots = $spots->sortBy([
            fn ($a, $b) => ($b['is_active'] && !$b['dummy'] ? 1 : 0) <=> ($a['is_active'] && !$a['dummy'] ? 1 : 0),
        ])->values();

        return \Inertia\Inertia::render('ParkingSpotListing', [
            'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
            'spots' => $spots,
            'locationStr' => $locationStr,
            'type' => $searchType,
            'start' => $start,
            'end' => $end,
            'startDate' => $request->input('startDate'),
            'endDate' => $request->input('endDate'),
            'startTime' => $request->input('startTime'),
            'endTime' => $request->input('endTime'),
            'days' => $request->input('days'),
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'type' => 'required|string|in:Driveway,Garage,Uncovered Lot,Covered Lot,Backyard',
            'price' => 'nullable|numeric|min:4',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_daily' => 'nullable|numeric|min:1',
            'total_spaces' => 'nullable|integer|min:1|max:100',
            'is24_7' => 'boolean',
            'features' => 'array',
            'additionalPoints' => 'array',
            'selectedDays' => 'array',
            'availFrom' => 'nullable|string',
            'availTo' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'contact_number' => 'required|string|regex:/^\(\d{3}\) \d{3}-\d{4}$/',
        ], [
            'contact_number.regex' => 'The contact number must be a valid 10-digit Canadian phone number in the format (555) 555-5555.',
        ]);

        if (is_null($request->price) && is_null($request->price_daily) && is_null($request->price_monthly)) {
            return back()->withErrors(['price' => 'At least one pricing option (Hourly, Daily, or Monthly) must be enabled.'])->withInput();
        }

        $spot = ParkingSpot::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'parking_type' => $validated['type'],
            'total_spaces' => max(1, (int) $request->input('total_spaces', 1)),
            'price_hourly' => $validated['price'] ?? null,
            'price_monthly' => $validated['price_monthly'] ?? null,
            'price_daily' => $validated['price_daily'] ?? null,
            'is_24_7' => $validated['is24_7'] ?? false,
            'features' => $validated['features'] ?? [],
            'additional_points' => $validated['additionalPoints'] ?? [],
            'contact_number' => $validated['contact_number'],
            'is_active' => false,
            'is_approved' => false,
            'service_fee_percentage' => 10.00,
            'service_fee_monthly_percentage' => 30.00,
        ]);

        if (!empty($validated['selectedDays'])) {
            // Note: time HTML input returns H:i, database accepts it
            foreach ($validated['selectedDays'] as $day) {
                ParkingSpotAvailability::create([
                    'parking_spot_id' => $spot->id,
                    'day_of_week' => $day,
                    'start_time' => $validated['availFrom'],
                    'end_time' => $validated['availTo'],
                ]);
            }
        }

        if ($files = $request->file('photos')) {
            // Ensure it's treated as an array
            $files = is_array($files) ? $files : [$files];
            foreach ($files as $photo) {
                // Store safely in storage/app/public/parking_spots
                $path = $photo->store('parking_spots', 'public');
                ParkingSpotPhoto::create([
                    'parking_spot_id' => $spot->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Parking spot submitted successfully! It is pending admin approval and will be activated once approved.');
    }

    public function show($id, Request $request)
    {
        $spot = ParkingSpot::with(['photos', 'availabilities'])->findOrFail($id);

        if (!$spot->is_approved && $spot->user_id !== Auth::id()) {
            abort(404);
        }

        $start = $request->input('start');
        $end = $request->input('end');
        $serviceFee = 5.00; // backend controlled fee
        $serviceFeeRate = (float) ($spot->service_fee_percentage ?? 10.00);
        $serviceFeeMonthlyRate = (float) ($spot->service_fee_monthly_percentage ?? 30.00);

        $photos = $spot->relationLoaded('photos') ? $spot->getRelation('photos') : collect([]);
        $firstPhoto = $photos->first();

        $availDays = $spot->availabilities->pluck('day_of_week')->unique()->values()->toArray();
        $availHours = 'Not specified';
        if ($spot->availabilities->isNotEmpty()) {
            $firstAvail = $spot->availabilities->first();
            try {
                $availHours = \Carbon\Carbon::parse($firstAvail->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($firstAvail->end_time)->format('h:i A');
            } catch (\Exception $e) {
                $availHours = $firstAvail->start_time . ' - ' . $firstAvail->end_time;
            }
        }

        $totalSpaces = (int) ($spot->total_spaces ?? 1);
        $availableSpaces = $this->getAvailableSpaces($spot, $request);
        $requestedSpaces = max(1, (int) $request->input('spaces', 1));

        $formattedSpot = [
            'id' => $spot->id,
            'address' => $spot->address . ($spot->city ? ', ' . $spot->city : ''),
            'total_spaces' => $totalSpaces,
            'available_spaces' => $availableSpaces,
            'rating' => 4.8,
            'reviews' => 6.5,
            'price' => $request->input('type') === 'monthly'
                ? $spot->price_monthly
                : ($request->input('type') === 'recurring'
                    ? ($spot->price_daily ?? $spot->price_hourly)
                    : $spot->price_hourly),
            'price_hourly' => $spot->price_hourly,
            'price_monthly' => $spot->price_monthly,
            'price_daily' => $spot->price_daily,
            'image' => $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=1200',
            'photos' => $photos->map(fn($p) => asset('storage/' . $p->image_path))->toArray(),
            'availDays' => !empty($availDays) ? $availDays : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'availHours' => $spot->is_24_7 ? '24/7' : $availHours,
            'features' => $spot->features ?? [],
            'additionalPoints' => $spot->additional_points ?? []
        ];

        return \Inertia\Inertia::render('ParkingSpotDetails', [
            'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
            'spot' => $formattedSpot,
            'type' => $request->input('type', 'one-time'),
            'start' => $start,
            'end' => $end,
            'startDate' => $request->input('startDate'),
            'endDate' => $request->input('endDate'),
            'startTime' => $request->input('startTime'),
            'endTime' => $request->input('endTime'),
            'days' => $request->input('days'),
            'totalSpaces' => $totalSpaces,
            'availableSpaces' => $availableSpaces,
            'spaces' => min($availableSpaces > 0 ? $availableSpaces : 1, $requestedSpaces),
            'serviceFee' => $serviceFee,
            'serviceFeeRate' => $serviceFeeRate,
            'serviceFeeMonthlyRate' => $serviceFeeMonthlyRate
        ]);
    }

    public function book($id, Request $request)
    {
        $spot = ParkingSpot::with(['photos'])->findOrFail($id);

        if (!$spot->is_approved && $spot->user_id !== Auth::id()) {
            abort(404);
        }

        $type = $request->input('type', 'one-time');
        $start = $request->input('start');
        $end = $request->input('end');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');
        $days = $request->input('days');

        $serviceFee = 5.00; // backend controlled fee
        $serviceFeeRate = (float) ($spot->service_fee_percentage ?? 10.00);
        $serviceFeeMonthlyRate = (float) ($spot->service_fee_monthly_percentage ?? 30.00);

        $photos = $spot->relationLoaded('photos') ? $spot->getRelation('photos') : collect([]);
        $firstPhoto = $photos->first();

        $totalSpaces = (int) ($spot->total_spaces ?? 1);
        $availableSpaces = $this->getAvailableSpaces($spot, $request);
        $requestedSpaces = max(1, (int) $request->input('spaces', 1));

        $formattedSpot = [
            'id' => $spot->id,
            'address' => $spot->address . ($spot->city ? ', ' . $spot->city : ''),
            'total_spaces' => $totalSpaces,
            'available_spaces' => $availableSpaces,
            'price' => $type === 'monthly'
                ? $spot->price_monthly
                : ($type === 'recurring'
                    ? ($spot->price_daily ?? $spot->price_hourly)
                    : $spot->price_hourly),
            'price_hourly' => $spot->price_hourly,
            'price_monthly' => $spot->price_monthly,
            'price_daily' => $spot->price_daily,
            'city' => $spot->city,
            'image' => $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=1200',
        ];

        $vehicles = \Illuminate\Support\Facades\Auth::user()->vehicles;

        return \Inertia\Inertia::render('BookSpot', [
            'spot' => $formattedSpot,
            'type' => $type,
            'start' => $start,
            'end' => $end,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'startTimeForm' => $startTime,
            'endTimeForm' => $endTime,
            'days' => $days,
            'totalSpaces' => $totalSpaces,
            'availableSpaces' => $availableSpaces,
            'spaces' => min($availableSpaces > 0 ? $availableSpaces : 1, $requestedSpaces),
            'serviceFee' => $serviceFee,
            'serviceFeeRate' => $serviceFeeRate,
            'serviceFeeMonthlyRate' => $serviceFeeMonthlyRate,
            'vehicles' => $vehicles,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    public function toggleStatus(ParkingSpot $spot)
    {
        if ($spot->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$spot->is_approved) {
            return back()->withErrors(['error' => 'This parking spot is pending admin approval and cannot be activated yet.']);
        }

        $spot->is_active = !$spot->is_active;
        $spot->save();

        return back()->with('success', 'Status updated successfully.');
    }

    public function edit($id)
    {
        $spot = ParkingSpot::with(['photos', 'availabilities'])->findOrFail($id);

        if ($spot->user_id !== Auth::id()) {
            abort(403);
        }

        $availDays = $spot->availabilities->pluck('day_of_week')->toArray();
        $startTime = $spot->availabilities->first()?->start_time;
        $endTime = $spot->availabilities->first()?->end_time;

        return \Inertia\Inertia::render('EditParkingSpot', [
            'spot' => [
                'id' => $spot->id,
                'title' => $spot->title,
                'address' => $spot->address,
                'city' => $spot->city,
                'state' => $spot->state,
                'country' => $spot->country,
                'latitude' => $spot->latitude,
                'longitude' => $spot->longitude,
                'parking_type' => $spot->parking_type,
                'total_spaces' => (int) ($spot->total_spaces ?? 1),
                'price_hourly' => $spot->price_hourly,
                'price_monthly' => $spot->price_monthly,
                'is_24_7' => $spot->is_24_7,
                'features' => $spot->features,
                'additional_points' => $spot->additional_points,
                'contact_number' => $spot->contact_number,
                'selectedDays' => $availDays,
                'availFrom' => $startTime ? \Carbon\Carbon::parse($startTime)->format('H:i') : '',
                'availTo' => $endTime ? \Carbon\Carbon::parse($endTime)->format('H:i') : '',
                'photos' => collect($spot->photos ?? [])->map(fn($p) => ['id' => $p->id, 'url' => asset('storage/' . $p->image_path)])
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $spot = ParkingSpot::findOrFail($id);

        if ($spot->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'type' => 'required|string|in:Driveway,Garage,Uncovered Lot,Covered Lot,Backyard',
            'total_spaces' => 'nullable|integer|min:1|max:100',
            'price' => 'nullable|numeric|min:4',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_daily' => 'nullable|numeric|min:1',
            'is24_7' => 'boolean',
            'features' => 'array',
            'additionalPoints' => 'array',
            'selectedDays' => 'array',
            'availFrom' => 'nullable|string',
            'availTo' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'removePhotos' => 'nullable|array',
            'removePhotos.*' => 'exists:parking_spot_photos,id',
            'contact_number' => 'required|string|regex:/^\(\d{3}\) \d{3}-\d{4}$/',
        ], [
            'contact_number.regex' => 'The contact number must be a valid 10-digit Canadian phone number in the format (555) 555-5555.',
        ]);

        if (is_null($request->price) && is_null($request->price_daily) && is_null($request->price_monthly)) {
            return back()->withErrors(['price' => 'At least one pricing option (Hourly, Daily, or Monthly) must be enabled.'])->withInput();
        }

        $spot->update([
            'title' => $validated['title'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? $spot->city,
            'state' => $validated['state'] ?? $spot->state,
            'country' => $validated['country'] ?? $spot->country,
            'latitude' => $validated['latitude'] ?? $spot->latitude,
            'longitude' => $validated['longitude'] ?? $spot->longitude,
            'parking_type' => $validated['type'],
            'total_spaces' => max(1, (int) $request->input('total_spaces', $spot->total_spaces ?? 1)),
            'price_hourly' => $validated['price'] ?? null,
            'price_monthly' => $validated['price_monthly'] ?? null,
            'price_daily' => $validated['price_daily'] ?? null,
            'is_24_7' => $validated['is24_7'] ?? false,
            'features' => $validated['features'] ?? [],
            'additional_points' => $validated['additionalPoints'] ?? [],
            'contact_number' => $validated['contact_number'],
        ]);

        // Update availabilities
        $spot->availabilities()->delete();
        if (!empty($validated['selectedDays'])) {
            foreach ($validated['selectedDays'] as $day) {
                ParkingSpotAvailability::create([
                    'parking_spot_id' => $spot->id,
                    'day_of_week' => $day,
                    'start_time' => $validated['availFrom'],
                    'end_time' => $validated['availTo'],
                ]);
            }
        }

        // Handle removed photos
        if (!empty($validated['removePhotos'])) {
            $photosToRemove = ParkingSpotPhoto::whereIn('id', $validated['removePhotos'])->where('parking_spot_id', $spot->id)->get();
            foreach ($photosToRemove as $photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->image_path);
                $photo->delete();
            }
        }

        // Handle new photos
        if ($files = $request->file('photos')) {
            $files = is_array($files) ? $files : [$files];
            foreach ($files as $photo) {
                $path = $photo->store('parking_spots', 'public');
                ParkingSpotPhoto::create([
                    'parking_spot_id' => $spot->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('spots.my-listings')->with('success', 'Parking spot updated successfully!');
    }

    public function destroy($id)
    {
        $spot = ParkingSpot::with('photos')->findOrFail($id);

        if ($spot->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete photos from storage
        $photos = $spot->photos ?? collect([]);
        foreach ($photos as $photo) {
            if ($photo->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->image_path);
            }
        }

        $spot->delete();

        return redirect()->route('spots.my-listings')->with('success', 'Parking spot deleted successfully!');
    }

    public function bookings($id)
    {
        $spot = ParkingSpot::where('user_id', Auth::id())
            ->with(['bookings.user'])
            ->findOrFail($id);

        return response()->json($spot->bookings);
    }

    private function getAvailableSpaces(ParkingSpot $spot, Request $request): int
    {
        $totalSpaces = (int) ($spot->total_spaces ?? 1);
        $type = $request->input('type', 'one-time');
        $start = $request->input('start');
        $end = $request->input('end');
        $bookedSpaces = 0;

        if ($type === 'recurring') {
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $startTime = $request->input('startTime');
            $endTime = $request->input('endTime');
            $days = explode(',', $request->input('days', ''));
            $timezone = $request->input('timezone', config('app.timezone'));

            if ($startDate && $endDate && $startTime && $endTime && !empty($days)) {
                $current = \Carbon\Carbon::parse($startDate, $timezone);
                $endRange = \Carbon\Carbon::parse($endDate, $timezone);
                $maxBooked = 0;

                while ($current->lte($endRange)) {
                    if (in_array($current->format('D'), $days)) {
                        $startCarbon = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $startTime, $timezone);
                        $endCarbon = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $endTime, $timezone);
                        if ($endCarbon->lte($startCarbon)) {
                            $endCarbon->addDay();
                        }
                        $sUtc = $startCarbon->setTimezone('UTC')->toDateTimeString();
                        $eUtc = $endCarbon->setTimezone('UTC')->toDateTimeString();

                        $dayBooked = (int) $spot->bookings()
                            ->where('status', '!=', 'cancelled')
                            ->where('start_time', '<', $eUtc)
                            ->where('end_time', '>', $sUtc)
                            ->sum('spaces_count');

                        if ($dayBooked > $maxBooked) {
                            $maxBooked = $dayBooked;
                        }
                    }
                    $current->addDay();
                }
                $bookedSpaces = $maxBooked;
            }
        } elseif ($type === 'monthly' && $request->input('startDate') && $request->input('endDate')) {
            $timezone = $request->input('timezone', config('app.timezone'));
            $sUtc = \Carbon\Carbon::parse($request->input('startDate'), $timezone)->setTimezone('UTC')->toDateTimeString();
            $eUtc = \Carbon\Carbon::parse($request->input('endDate'), $timezone)->setTimezone('UTC')->toDateTimeString();
            $bookedSpaces = (int) $spot->bookings()
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '<', $eUtc)
                ->where('end_time', '>', $sUtc)
                ->sum('spaces_count');
        } elseif ($start && $end) {
            $sUtc = \Carbon\Carbon::parse($start)->setTimezone('UTC')->toDateTimeString();
            $eUtc = \Carbon\Carbon::parse($end)->setTimezone('UTC')->toDateTimeString();
            $bookedSpaces = (int) $spot->bookings()
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '<', $eUtc)
                ->where('end_time', '>', $sUtc)
                ->sum('spaces_count');
        }

        return max(0, $totalSpaces - $bookedSpaces);
    }
}
