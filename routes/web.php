<?php

use App\Http\Controllers\BhavBookingController;
use App\Http\Controllers\CustomerController;
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

Route::get('/buy-sell/bhav-booking', [BhavBookingController::class, 'index'])->name('bhav-booking.index');
Route::post('/bhav-bookings', [BhavBookingController::class, 'store'])->name('bhav-bookings.store');

Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

