<?php

use App\Http\Controllers\BhavBookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyRateController;
use App\Http\Controllers\PartyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Bhav Booking Main UI
Route::get('/buy-sell/bhav-booking', [BhavBookingController::class, 'index'])->name('bhav-booking.index');
Route::get('/buy-sell/bhav-booking/old', [BhavBookingController::class, 'old'])->name('bhav-booking.old');

// Today's Daily Bullion Rates
Route::get('/daily-rates/today', [DailyRateController::class, 'today'])->name('daily-rates.today');
Route::post('/daily-rates', [DailyRateController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('daily-rates.store');

// Customer Search, Recent & Creation (with throttling)
Route::get('/customers/search', [CustomerController::class, 'search'])
    ->middleware('throttle:120,1')
    ->name('customers.search');

Route::get('/customers/recent', [CustomerController::class, 'recent'])
    ->middleware('throttle:120,1')
    ->name('customers.recent');

Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('customers.store');

// Callback for route model binding failure on bhav-bookings
// Why: If a requested booking ID does not exist in the database,
// JSON requests receive HTTP 404 with {"message": "Booking not found"}
$missingBookingCallback = function (Request $request) {
    if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
        return response()->json(['message' => 'Booking not found'], 404);
    }
    abort(404, 'Booking not found');
};

// Bhav Booking List, Export & Store (with throttling)
// Note: /bhav-bookings/list and /export must come before /{bhavBooking} so they are not matched as an ID
Route::get('/bhav-bookings/list', [BhavBookingController::class, 'list'])
    ->middleware('throttle:120,1')
    ->name('bhav-bookings.list');

Route::get('/bhav-bookings/export', [BhavBookingController::class, 'export'])
    ->middleware('throttle:120,1')
    ->name('bhav-bookings.export');

Route::post('/bhav-bookings', [BhavBookingController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('bhav-bookings.store');

Route::get('/bhav-bookings/group/{groupId}/print', [BhavBookingController::class, 'printGroup'])
    ->name('bhav-bookings.group.print');

// Bhav Booking View, Edit, Delete, Print, and Action Logging via Route Model Binding ({bhavBooking})
Route::get('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'show'])
    ->name('bhav-bookings.show')
    ->missing($missingBookingCallback);

Route::put('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'update'])
    ->middleware('throttle:30,1')
    ->name('bhav-bookings.update')
    ->missing($missingBookingCallback);

Route::delete('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'destroy'])
    ->middleware('throttle:30,1')
    ->name('bhav-bookings.destroy')
    ->missing($missingBookingCallback);

Route::get('/bhav-bookings/{bhavBooking}/print', [BhavBookingController::class, 'print'])
    ->name('bhav-bookings.print')
    ->missing($missingBookingCallback);

Route::post('/bhav-bookings/{bhavBooking}/log-action', [BhavBookingController::class, 'logAction'])
    ->middleware('throttle:60,1')
    ->name('bhav-bookings.log-action')
    ->missing($missingBookingCallback);

// Callback for route model binding failure on parties
// Returns JSON 404 "Party not found"
$missingPartyCallback = function (Request $request) {
    return response()->json(['message' => 'Party not found'], 404);
};

// Party Master Routes
Route::get('/parties', [PartyController::class, 'index'])->name('parties.index');
Route::get('/parties/list', [PartyController::class, 'list'])->name('parties.list');
Route::get('/parties/{customer}', [PartyController::class, 'show'])
    ->name('parties.show')
    ->missing($missingPartyCallback);
Route::put('/parties/{customer}', [PartyController::class, 'update'])
    ->name('parties.update')
    ->missing($missingPartyCallback);
Route::delete('/parties/{customer}', [PartyController::class, 'destroy'])
    ->name('parties.destroy')
    ->missing($missingPartyCallback);
