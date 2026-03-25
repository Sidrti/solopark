<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/search', [App\Http\Controllers\ParkingSpotController::class, 'index'])->name('search');

Route::get('/contact-us', function () {
    return Inertia::render('ContactUs', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('contact-us');

Route::get('/list-spot', function () {
    return Inertia::render('ListParkingSpot', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->middleware(['auth', 'verified'])->name('list-spot');

Route::post('/list-spot', [App\Http\Controllers\ParkingSpotController::class, 'store'])->middleware('auth');

Route::get('/spot/{id}', [App\Http\Controllers\ParkingSpotController::class, 'show'])->name('spot-details');

Route::get('/spot/{id}/book', [App\Http\Controllers\ParkingSpotController::class, 'book'])->middleware(['auth', 'verified'])->name('spot-book');

// Dashboard route removed

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');
    Route::post('/vehicles', [App\Http\Controllers\VehicleController::class, 'store'])->name('vehicles.store');
    
    // Bookings
    Route::get('/my-bookings', [App\Http\Controllers\BookingController::class, 'history'])->name('bookings.history');
    Route::post('/bookings', [App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/payment-intent', [App\Http\Controllers\BookingController::class, 'createPaymentIntent'])->name('bookings.payment-intent');
    Route::get('/bookings/{booking}', [App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');

    // My Listings
    Route::get('/my-listings', [App\Http\Controllers\ParkingSpotController::class, 'userListings'])->name('spots.my-listings');
    Route::patch('/spots/{spot}/toggle-status', [App\Http\Controllers\ParkingSpotController::class, 'toggleStatus'])->name('spots.toggle-status');
    Route::get('/spots/{spot}/bookings', [App\Http\Controllers\ParkingSpotController::class, 'bookings'])->name('spots.bookings');
});

require __DIR__ . '/auth.php';
