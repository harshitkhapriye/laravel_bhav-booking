<?php

namespace App\Exceptions;

use App\Models\BhavBooking;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Why: Return clean JSON 404 message "Booking not found" whenever a BhavBooking is not found in JSON requests
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                if ($e->getModel() === BhavBooking::class) {
                    return response()->json(['message' => 'Booking not found'], 404);
                }
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                $previous = $e->getPrevious();
                if ($previous instanceof ModelNotFoundException) {
                    if ($previous->getModel() === BhavBooking::class) {
                        return response()->json(['message' => 'Booking not found'], 404);
                    }
                }
            }
        });

        // Why: Friendly JSON message on HTTP 429 Too Many Requests for API routes
        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson() || $request->is('customers/*') || $request->is('bhav-bookings*') || $request->is('daily-rates*')) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down and try again shortly.',
                ], 429);
            }
        });
    }
}
