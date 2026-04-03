<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['spot.photos', 'vehicle'])
            ->latest()
            ->get()
            ->map(function ($booking) {
            $photos = $booking->spot->relationLoaded('photos') ? $booking->spot->getRelation('photos') : collect([]);
            $firstPhoto = $photos->first();
            $imageUrl = $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=400';

            return [
            'id' => $booking->id,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'total_price' => round($booking->total_price, 0),
            'status' => $booking->status,
            'timezone' => $booking->timezone,
            'is_recurring' => $booking->is_recurring,
            'recurring_group_id' => $booking->recurring_group_id,
            'spot' => [
            'id' => $booking->spot->id,
            'title' => $booking->spot->title,
            'address' => $booking->spot->address,
            'city' => $booking->spot->city,
            'image' => $imageUrl,
            ]
            ];
        });

        return Inertia::render('BookingHistory', [
            'bookings' => $bookings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'spot_id' => 'required|exists:parking_spots,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'mobile_number' => 'required|string|max:20',
            'subtotal' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'gateway_fee' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'timezone' => 'required|string',
            'type' => 'required|in:one-time,recurring,monthly',
            'start_time' => 'required_if:type,one-time',
            'end_time' => 'required_if:type,one-time',
            'startDate' => 'required_if:type,recurring,monthly',
            'endDate' => 'required_if:type,recurring,monthly',
            'startTime' => 'required_if:type,recurring',
            'endTime' => 'required_if:type,recurring',
            'days' => 'required_if:type,recurring',
            'payment_intent_id' => 'required|string',
        ]);

        if ($validated['type'] === 'one-time' || $validated['type'] === 'monthly') {
            $startTime = $validated['type'] === 'one-time'
                ? \Carbon\Carbon::parse($validated['start_time'])
                : \Carbon\Carbon::parse($validated['startDate'], $validated['timezone'])->startOfDay()->setTimezone('UTC');

            $endTime = $validated['type'] === 'one-time'
                ? \Carbon\Carbon::parse($validated['end_time'])
                : \Carbon\Carbon::parse($validated['endDate'], $validated['timezone'])->endOfDay()->setTimezone('UTC');

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'parking_spot_id' => $validated['spot_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'mobile_number' => $validated['mobile_number'],
                'subtotal' => $validated['subtotal'],
                'service_fee' => $validated['service_fee'],
                'tax' => $validated['tax'],
                'gateway_fee' => round($validated['gateway_fee'], 0),
                'total_price' => round($validated['total_price'], 0),
                'status' => 'confirmed',
                'timezone' => $validated['timezone'],
                'payment_intent_id' => $validated['payment_intent_id'],
            ]);
        }
        else {
            // Recurring Booking
            $groupId = \Illuminate\Support\Str::uuid()->toString();
            $days = explode(',', $validated['days']);
            $current = \Carbon\Carbon::parse($validated['startDate'], $validated['timezone']);
            $endDate = \Carbon\Carbon::parse($validated['endDate'], $validated['timezone']);

            $pendingBookings = [];
            while ($current->lte($endDate)) {
                if (in_array($current->format('D'), $days)) {
                    $startUtc = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $validated['startTime'], $validated['timezone'])->setTimezone('UTC');
                    $endUtc = \Carbon\Carbon::parse($current->format('Y-m-d') . ' ' . $validated['endTime'], $validated['timezone'])->setTimezone('UTC');

                    $pendingBookings[] = [
                        'user_id' => Auth::id(),
                        'parking_spot_id' => $validated['spot_id'],
                        'vehicle_id' => $validated['vehicle_id'],
                        'start_time' => $startUtc,
                        'end_time' => $endUtc,
                        'mobile_number' => $validated['mobile_number'],
                        'status' => 'confirmed',
                        'timezone' => $validated['timezone'],
                        'is_recurring' => true,
                        'recurring_group_id' => $groupId,
                        'payment_intent_id' => $validated['payment_intent_id'],
                    ];
                }
                $current->addDay();
            }

            $count = count($pendingBookings);
            if ($count === 0) {
                return back()->withErrors(['days' => 'No valid days found in range.']);
            }

            $perBookingSubtotal = $validated['subtotal'] / $count;
            $perBookingFee = $validated['service_fee'] / $count;
            $perBookingTax = $validated['tax'] / $count;
            $perBookingGateway = $validated['gateway_fee'] / $count;
            $perBookingTotal = $validated['total_price'] / $count;

            foreach ($pendingBookings as $index => $data) {
                $data['subtotal'] = $perBookingSubtotal;
                $data['service_fee'] = $perBookingFee;
                $data['tax'] = $perBookingTax;
                $data['gateway_fee'] = $perBookingGateway;
                $data['total_price'] = round($perBookingTotal, 0);

                $created = Booking::create($data);
                if ($index === 0) {
                    $booking = $created;
                }
            }
        }

        // Send Emails via Brevo SDK
        try {
            $booking->load(['user', 'spot.user', 'vehicle']);
            $brevo = new \App\Services\BrevoService();

            // Customer Email
            $customerHtml = view('emails.booking_confirmation', ['booking' => $booking])->render();
            $brevo->sendEmail(
                $booking->user->email,
                'Reservation Confirmed - Solopark',
                $customerHtml
            );

            // Owner Email
            $ownerHtml = view('emails.new_booking_alert', ['booking' => $booking])->render();
            $brevo->sendEmail(
                $booking->spot->user->email,
                'New Reservation for your Listing - Solopark',
                $ownerHtml
            );

        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send booking emails via Brevo: ' . $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking->id)->with('success', 'Reservation completed successfully!');
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'spot_id' => 'required|exists:parking_spots,id'
        ]);

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)($request->input('amount') * 100), // in cents
                'currency' => 'cad',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'user_id' => Auth::id(),
                    'spot_id' => $request->input('spot_id'),
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['spot.user', 'vehicle', 'spot.photos']);

        $photos = $booking->spot->relationLoaded('photos') ? $booking->spot->getRelation('photos') : collect([]);
        $firstPhoto = $photos->first();
        $imageUrl = $firstPhoto ? asset('storage/' . $firstPhoto->image_path) : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=1200';

        $formattedSpot = [
            'id' => $booking->spot->id,
            'title' => $booking->spot->title,
            'address' => $booking->spot->address . ($booking->spot->city ? ', ' . $booking->spot->city : ''),
            'city' => $booking->spot->city,
            'image' => $imageUrl,
            'latitude' => $booking->spot->latitude,
            'longitude' => $booking->spot->longitude,
            'owner' => [
                'name' => $booking->spot->user->name,
                'email' => $booking->spot->user->email,
            ]
        ];

        return Inertia::render('BookingDetails', [
            'booking' => [
                'id' => $booking->id,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'subtotal' => $booking->subtotal,
                'service_fee' => $booking->service_fee,
                'tax' => $booking->tax,
                'gateway_fee' => $booking->gateway_fee,
                'total_price' => round($booking->total_price, 0),
                'mobile_number' => $booking->mobile_number,
                'status' => $booking->status,
                'timezone' => $booking->timezone,
                'is_recurring' => $booking->is_recurring,
                'recurring_group_id' => $booking->recurring_group_id,
                'created_at' => $booking->created_at,
            ],
            'spot' => $formattedSpot,
            'vehicle' => $booking->vehicle,
            'stripeKey' => config('services.stripe.key')
        ]);
    }
}
