<?php

use App\Http\Controllers\BhavBookingController;
use App\Http\Controllers\CustomerController;
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

// Customer Search, Recent & Creation
Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::get('/customers/recent', [CustomerController::class, 'recent'])->name('customers.recent');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

// Callback for route model binding failure on bhav-bookings
// Why: If a requested booking ID does not exist in the database,
// JSON requests receive HTTP 404 with {"message": "Booking not found"}
$missingBookingCallback = function (Request $request) {
    if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
        return response()->json(['message' => 'Booking not found'], 404);
    }
    abort(404, 'Booking not found');
};

// Bhav Booking List & Store
// Note: /bhav-bookings/list must come before /{bhavBooking} so 'list' is not matched as an ID
Route::get('/bhav-bookings/list', [BhavBookingController::class, 'list'])->name('bhav-bookings.list');
Route::post('/bhav-bookings', [BhavBookingController::class, 'store'])->name('bhav-bookings.store');

// Bhav Booking View, Edit, Delete, Print via Route Model Binding ({bhavBooking})
Route::get('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'show'])
    ->name('bhav-bookings.show')
    ->missing($missingBookingCallback);

Route::put('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'update'])
    ->name('bhav-bookings.update')
    ->missing($missingBookingCallback);

Route::delete('/bhav-bookings/{bhavBooking}', [BhavBookingController::class, 'destroy'])
    ->name('bhav-bookings.destroy')
    ->missing($missingBookingCallback);

Route::get('/bhav-bookings/{bhavBooking}/print', [BhavBookingController::class, 'print'])
    ->name('bhav-bookings.print')
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

