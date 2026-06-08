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
                    'is_active' => $spot->is_active,
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

        $query = ParkingSpot::query()->where('is_active', true)->with('photos');

        $latitude = $lat ?: 43.6532; // Default to Toronto
        $longitude = $lng ?: -79.3832;

        if ($lat && $lng) {
            $query->select('*')
                ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                ->orderBy('distance');
        } else {
            $query->latest();
        }

        $timezone = $request->input('timezone', config('app.timezone'));
        $searchType = $request->input('type', 'one-time');

        if ($searchType === 'one-time' && $start && $end) {
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

                // Filter out spots with overlapping bookings
                $query->whereDoesntHave('bookings', function ($q) use ($startUtc, $endUtc) {
                    $q->where('start_time', '<', $endUtc)
                        ->where('end_time', '>', $startUtc);
                });
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        } elseif ($searchType === 'recurring') {
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
                    $query->where(function ($q) use ($days, $startTime, $endTime, $startDate, $endDate, $timezone) {
                        foreach ($days as $day) {
                            $q->whereHas(
                                'availabilities',
                                function ($subQ) use ($day, $startTime, $endTime) {
                                    $subQ->where('day_of_week', $day)
                                        ->where('start_time', '<=', $startTime)
                                        ->where('end_time', '>=', $endTime);
                                }
                            );
                        }
                    });

                    // Check for overlaps on each requested day in the range
                    $current = \Carbon\Carbon::parse($startDate, $timezone);
                    $endRange = \Carbon\Carbon::parse($endDate, $timezone);

                    while ($current->lte($endRange)) {
                        if (in_array($current->format('D'), $days)) {
                            $dayStartUtc = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $startTime, $timezone)->setTimezone('UTC')->toDateTimeString();
                            $dayEndUtc = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $endTime, $timezone)->setTimezone('UTC')->toDateTimeString();

                            $query->whereDoesntHave('bookings', function ($q) use ($dayStartUtc, $dayEndUtc) {
                                $q->where('start_time', '<', $dayEndUtc)
                                    ->where('end_time', '>', $dayStartUtc);
                            });
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

                $query->whereDoesntHave('bookings', function ($q) use ($startUtc, $endUtc) {
                    $q->where('start_time', '<', $endUtc)
                        ->where('end_time', '>', $startUtc);
                });
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
                $baseCost = ($spot->price_hourly / 2) * $durationUnits;
            }

            $serviceRate = ($searchType === 'monthly') ? 0.30 : 0.10;
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
                'dummy' => (bool)$spot->dummy,
            ];
        });

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
            'price' => 'required|numeric|min:0',
            'price_monthly' => 'nullable|numeric|min:0',
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
            'price_hourly' => $validated['price'],
            'price_monthly' => $validated['price_monthly'] ?? null,
            'is_24_7' => $validated['is24_7'] ?? false,
            'features' => $validated['features'] ?? [],
            'additional_points' => $validated['additionalPoints'] ?? [],
            'contact_number' => $validated['contact_number'],
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

        return redirect()->back()->with('success', 'Parking spot listed successfully!');
    }

    public function show($id, Request $request)
    {
        $spot = ParkingSpot::with(['photos', 'availabilities'])->findOrFail($id);

        $start = $request->input('start');
        $end = $request->input('end');
        $serviceFee = 5.00; // backend controlled fee

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

        $formattedSpot = [
            'id' => $spot->id,
            'address' => $spot->address . ($spot->city ? ', ' . $spot->city : ''),
            'rating' => 4.8,
            'reviews' => 6.5,
            'price' => $request->input('type') === 'monthly' ? $spot->price_monthly : $spot->price_hourly,
            'price_hourly' => $spot->price_hourly,
            'price_monthly' => $spot->price_monthly,
            'image' => $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=1200',
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
            'serviceFee' => $serviceFee
        ]);
    }

    public function book($id, Request $request)
    {
        $spot = ParkingSpot::with(['photos'])->findOrFail($id);

        $type = $request->input('type', 'one-time');
        $start = $request->input('start');
        $end = $request->input('end');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');
        $days = $request->input('days');

        $serviceFee = 5.00; // backend controlled fee

        $photos = $spot->relationLoaded('photos') ? $spot->getRelation('photos') : collect([]);
        $firstPhoto = $photos->first();

        $formattedSpot = [
            'id' => $spot->id,
            'address' => $spot->address . ($spot->city ? ', ' . $spot->city : ''),
            'price' => $type === 'monthly' ? $spot->price_monthly : $spot->price_hourly,
            'price_hourly' => $spot->price_hourly,
            'price_monthly' => $spot->price_monthly,
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
            'serviceFee' => $serviceFee,
            'vehicles' => $vehicles,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    public function toggleStatus(ParkingSpot $spot)
    {
        if ($spot->user_id !== Auth::id()) {
            abort(403);
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
            'price' => 'required|numeric|min:0',
            'price_monthly' => 'nullable|numeric|min:0',
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

        $spot->update([
            'title' => $validated['title'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? $spot->city,
            'state' => $validated['state'] ?? $spot->state,
            'country' => $validated['country'] ?? $spot->country,
            'latitude' => $validated['latitude'] ?? $spot->latitude,
            'longitude' => $validated['longitude'] ?? $spot->longitude,
            'parking_type' => $validated['type'],
            'price_hourly' => $validated['price'],
            'price_monthly' => $validated['price_monthly'] ?? null,
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
}
