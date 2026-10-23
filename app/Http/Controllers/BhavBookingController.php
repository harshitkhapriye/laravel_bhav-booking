<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListBhavBookingRequest;
use App\Http\Requests\StoreBhavBookingRequest;
use App\Http\Requests\UpdateBhavBookingRequest;
use App\Models\BhavBooking;
use App\Services\BhavBookingService;
use Illuminate\Http\JsonResponse;

class BhavBookingController extends Controller
{
    /**
     * Dedicated service for Bhav Booking calculations and business rules.
     */
    protected BhavBookingService $bhavBookingService;

    /**
     * Inject the service via controller constructor.
     * Why: Centralizes calculations and operations in one testable service class.
     */
    public function __construct(BhavBookingService $bhavBookingService)
    {
        $this->bhavBookingService = $bhavBookingService;
    }

    /**
     * Display the Bhav Booking UI page.
     */
    public function index()
    {
        return view('buy-sell.bhav-booking');
    }

    /**
     * Display the Old Bhav Booking list page.
     */
    public function old()
    {
        return view('buy-sell.bhav-booking-old');
    }

    /**
     * Get a paginated list of Bhav Bookings with filters and overall totals.
     *
     * @param ListBhavBookingRequest $request
     * @return JsonResponse
     */
    public function list(ListBhavBookingRequest $request): JsonResponse
    {
        $query = BhavBooking::query();

        // 1. Filter by view mode (current / old)
        // view=current -> status PENDING or PARTIAL
        // view=old -> status DELIVERED or CANCELLED
        $view = strtolower($request->input('view', 'current') ?: 'current');
        if ($view === 'old') {
            $query->whereIn('status', ['DELIVERED', 'CANCELLED']);
        } else {
            $query->whereIn('status', ['PENDING', 'PARTIAL']);
        }

        // 2. Filter by metal (GOLD / SILVER)
        $metal = strtoupper($request->input('metal', 'ALL') ?: 'ALL');
        if (in_array($metal, ['GOLD', 'SILVER'], true)) {
            $query->where('metal', $metal);
        }

        // 3. Filter by entry_type (ONLY_ENTRY / BHAV_CUT_LEDGER)
        $entryType = strtoupper($request->input('entry_type', 'ALL') ?: 'ALL');
        if (in_array($entryType, ['ONLY_ENTRY', 'BHAV_CUT_LEDGER'], true)) {
            $query->where('entry_type', $entryType);
        }

        // 4. Date range filter on booked_at
        if ($request->filled('date_from')) {
            $query->whereDate('booked_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booked_at', '<=', $request->input('date_to'));
        }

        // 5. Party search across customer name, mobile, customer_no, and booking_no
        if ($request->filled('party')) {
            $party = trim($request->input('party'));
            $query->where(function ($q) use ($party) {
                $q->where('booking_no', 'like', "%{$party}%")
                  ->orWhereHas('customer', function ($cq) use ($party) {
                      $cq->where('name', 'like', "%{$party}%")
                         ->orWhere('mobile', 'like', "%{$party}%")
                         ->orWhere('customer_no', 'like', "%{$party}%");
                  });
            });
        }

        // Compute totals with per-metal split across all matching rows before pagination
        $totalsQuery = clone $query;
        $totals = [
            'buy_weight_gold'    => (float) (clone $totalsQuery)->where('trade_type', 'BUY')->where('metal', 'GOLD')->sum('total_weight'),
            'buy_weight_silver'  => (float) (clone $totalsQuery)->where('trade_type', 'BUY')->where('metal', 'SILVER')->sum('total_weight'),
            'buy_amount'         => (float) (clone $totalsQuery)->where('trade_type', 'BUY')->sum('total_amount'),
            'sell_weight_gold'   => (float) (clone $totalsQuery)->where('trade_type', 'SELL')->where('metal', 'GOLD')->sum('total_weight'),
            'sell_weight_silver' => (float) (clone $totalsQuery)->where('trade_type', 'SELL')->where('metal', 'SILVER')->sum('total_weight'),
            'sell_amount'        => (float) (clone $totalsQuery)->where('trade_type', 'SELL')->sum('total_amount'),
            'given_total'        => (float) (clone $totalsQuery)->sum('given_amount'),
            'received_total'     => (float) (clone $totalsQuery)->sum('received_amount'),
        ];

        // 6. Eager load customer and items, sort newest booked_at first
        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->with([
            'customer:id,customer_no,name,mobile,city',
            'items:id,bhav_booking_id,sort_order,weight,rate,amount'
        ])
        ->orderByDesc('booked_at')
        ->orderByDesc('id')
        ->paginate($perPage);

        // Format rows according to frontend expectations
        $rows = collect($paginator->items())->map(function (BhavBooking $booking) {
            return [
                'id'               => $booking->id,
                'booking_no'       => $booking->booking_no,
                'customer'         => $booking->customer ? [
                    'id'          => $booking->customer->id,
                    'customer_no' => $booking->customer->customer_no,
                    'name'        => $booking->customer->name,
                    'mobile'      => $booking->customer->mobile,
                    'city'        => $booking->customer->city,
                ] : null,
                'trade_type'       => $booking->trade_type,
                'entry_type'       => $booking->entry_type,
                'metal'            => $booking->metal,
                'total_weight'     => (float) $booking->total_weight,
                'total_amount'     => (float) $booking->total_amount,
                'delivered_weight' => (float) $booking->delivered_weight,
                'pending_weight'   => (float) max(0, $booking->total_weight - $booking->delivered_weight),
                'given_amount'     => (float) $booking->given_amount,
                'received_amount'  => (float) $booking->received_amount,
                'status'           => $booking->status,
                'narration'        => $booking->narration,
                'send_whatsapp'    => (bool) $booking->send_whatsapp,
                'booked_at'        => $booking->booked_at ? $booking->booked_at->toIso8601String() : null,
                'items'            => $booking->items->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'sort_order' => $item->sort_order,
                        'weight'     => (float) $item->weight,
                        'rate'       => (float) $item->rate,
                        'amount'     => (float) $item->amount,
                    ];
                })->all(),
            ];
        });

        return response()->json([
            'rows'       => $rows,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
            ],
            'totals'     => $totals,
        ]);
    }

    /**
     * Store a newly created Bhav Booking in storage.
     *
     * @param StoreBhavBookingRequest $request
     * @return JsonResponse
     */
    public function store(StoreBhavBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->bhavBookingService->create($validated);

        return response()->json($result['booking'], $result['created'] ? 201 : 200);
    }

    /**
     * Display the specified Bhav Booking details.
     *
     * @param BhavBooking $bhavBooking
     * @return JsonResponse
     */
    public function show(BhavBooking $bhavBooking): JsonResponse
    {
        $bhavBooking->load([
            'customer:id,customer_no,name,mobile,city',
            'items:id,bhav_booking_id,sort_order,weight,rate,amount'
        ]);

        return response()->json($bhavBooking);
    }

    /**
     * Update the specified Bhav Booking in storage.
     * Allowed only if status = PENDING and delivered_weight = 0 and given_amount = 0 and received_amount = 0.
     *
     * @param UpdateBhavBookingRequest $request
     * @param BhavBooking $bhavBooking
     * @return JsonResponse
     */
    public function update(UpdateBhavBookingRequest $request, BhavBooking $bhavBooking): JsonResponse
    {
        if (! $this->bhavBookingService->canModify($bhavBooking)) {
            return response()->json([
                'message' => 'This booking cannot be edited because it is no longer pending or has transactions.'
            ], 422);
        }

        $validated = $request->validated();
        $booking = $this->bhavBookingService->update($bhavBooking, $validated);

        return response()->json($booking);
    }

    /**
     * Remove the specified Bhav Booking from storage (soft delete).
     * Allowed only if status = PENDING and delivered_weight = 0 and given_amount = 0 and received_amount = 0.
     *
     * @param BhavBooking $bhavBooking
     * @return JsonResponse
     */
    public function destroy(BhavBooking $bhavBooking): JsonResponse
    {
        if (! $this->bhavBookingService->canModify($bhavBooking)) {
            return response()->json([
                'message' => 'This booking cannot be deleted because it is no longer pending or has transactions.'
            ], 422);
        }

        $this->bhavBookingService->delete($bhavBooking);

        return response()->json([
            'message' => 'Booking deleted successfully.'
        ]);
    }

    /**
     * Print voucher view for a single booking.
     *
     * @param BhavBooking $bhavBooking
     */
    public function print(BhavBooking $bhavBooking)
    {
        $booking = $bhavBooking->load(['customer', 'items']);
        return view('buy-sell.bhav-booking-print', compact('booking'));
    }
}
