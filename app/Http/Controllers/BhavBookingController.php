<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListBhavBookingRequest;
use App\Http\Requests\StoreBhavBookingRequest;
use App\Http\Requests\UpdateBhavBookingRequest;
use App\Models\BhavBooking;
use App\Models\Customer;
use App\Services\BhavBookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Build base query with all filters applied.
     * Shared between list API and CSV export.
     *
     * @return Builder
     */
    protected function getFilteredQuery(ListBhavBookingRequest $request)
    {
        $query = BhavBooking::query();

        // 1. Filter by view mode (current / old)
        $view = strtolower($request->input('view', 'current') ?: 'current');
        if ($view === 'old') {
            $query->whereIn('status', ['DELIVERED', 'CANCELLED']);
        } else {
            $query->whereIn('status', ['PENDING', 'PARTIAL']);
        }

        // 2. Filter by trade_type (BUY / SELL)
        $tradeType = strtoupper($request->input('trade_type', 'ALL') ?: 'ALL');
        if (in_array($tradeType, ['BUY', 'SELL'], true)) {
            $query->where('trade_type', $tradeType);
        }

        // 3. Filter by metal (GOLD / SILVER)
        $metal = strtoupper($request->input('metal', 'ALL') ?: 'ALL');
        if (in_array($metal, ['GOLD', 'SILVER'], true)) {
            $query->where('metal', $metal);
        }

        // 4. Filter by entry_type (ONLY_ENTRY / BHAV_CUT_LEDGER)
        $entryType = strtoupper($request->input('entry_type', 'ALL') ?: 'ALL');
        if (in_array($entryType, ['ONLY_ENTRY', 'BHAV_CUT_LEDGER'], true)) {
            $query->where('entry_type', $entryType);
        }

        // 5. Date range filter on booked_at
        if ($request->filled('date_from')) {
            $query->whereDate('booked_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booked_at', '<=', $request->input('date_to'));
        }

        // 6. Party search across customer name, mobile, customer_no, and booking_no
        if ($request->filled('party')) {
            $party = trim($request->input('party'));
            $matchingCustomerIds = Customer::query()
                ->select('id')
                ->where('name', 'like', "%{$party}%")
                ->orWhere('mobile', 'like', "{$party}%")
                ->orWhere('customer_no', 'like', "{$party}%");

            $query->where(function ($q) use ($party, $matchingCustomerIds) {
                $q->where('booking_no', 'like', "%{$party}%")
                    ->orWhereIn('customer_id', $matchingCustomerIds);
            });
        }

        return $query;
    }

    /**
     * Get a paginated list of Bhav Bookings with filters and overall totals.
     */
    public function list(ListBhavBookingRequest $request): JsonResponse
    {
        $query = $this->getFilteredQuery($request);

        // Compute totals with a SINGLE aggregate query using SUM(CASE WHEN ...) for all 8 values
        $aggregates = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN trade_type = 'BUY' AND metal = 'GOLD' THEN total_weight ELSE 0 END), 0) as buy_weight_gold,
            COALESCE(SUM(CASE WHEN trade_type = 'BUY' AND metal = 'SILVER' THEN total_weight ELSE 0 END), 0) as buy_weight_silver,
            COALESCE(SUM(CASE WHEN trade_type = 'BUY' THEN total_amount ELSE 0 END), 0) as buy_amount,
            COALESCE(SUM(CASE WHEN trade_type = 'SELL' AND metal = 'GOLD' THEN total_weight ELSE 0 END), 0) as sell_weight_gold,
            COALESCE(SUM(CASE WHEN trade_type = 'SELL' AND metal = 'SILVER' THEN total_weight ELSE 0 END), 0) as sell_weight_silver,
            COALESCE(SUM(CASE WHEN trade_type = 'SELL' THEN total_amount ELSE 0 END), 0) as sell_amount,
            COALESCE(SUM(given_amount), 0) as given_total,
            COALESCE(SUM(received_amount), 0) as received_total
        ")->first();

        $totals = [
            'buy_weight_gold' => (float) ($aggregates->buy_weight_gold ?? 0),
            'buy_weight_silver' => (float) ($aggregates->buy_weight_silver ?? 0),
            'buy_amount' => (float) ($aggregates->buy_amount ?? 0),
            'sell_weight_gold' => (float) ($aggregates->sell_weight_gold ?? 0),
            'sell_weight_silver' => (float) ($aggregates->sell_weight_silver ?? 0),
            'sell_amount' => (float) ($aggregates->sell_amount ?? 0),
            'given_total' => (float) ($aggregates->given_total ?? 0),
            'received_total' => (float) ($aggregates->received_total ?? 0),
        ];

        // 6. Eager load customer and items, sort newest booked_at first
        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->select([
            'id',
            'booking_no',
            'customer_id',
            'trade_type',
            'entry_type',
            'metal',
            'total_weight',
            'total_amount',
            'delivered_weight',
            'given_amount',
            'received_amount',
            'status',
            'narration',
            'booked_at',
        ])
            ->with([
                'customer:id,customer_no,name,mobile,city',
                'items:id,bhav_booking_id,sort_order,weight,rate,amount',
                'payments:id,bhav_booking_id,mode,online_method,reference_no,amount,source,sort_order,paid_at',
            ])
            ->orderByDesc('booked_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        // Format rows according to frontend expectations
        $rows = collect($paginator->items())->map(function (BhavBooking $booking) {
            return [
                'id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'customer' => $booking->customer ? [
                    'id' => $booking->customer->id,
                    'customer_no' => $booking->customer->customer_no,
                    'name' => $booking->customer->name,
                    'mobile' => $booking->customer->mobile,
                    'city' => $booking->customer->city,
                ] : null,
                'trade_type' => $booking->trade_type,
                'entry_type' => $booking->entry_type,
                'metal' => $booking->metal,
                'total_weight' => (float) $booking->total_weight,
                'total_amount' => (float) $booking->total_amount,
                'delivered_weight' => (float) $booking->delivered_weight,
                'pending_weight' => (float) max(0, $booking->total_weight - $booking->delivered_weight),
                'given_amount' => (float) $booking->given_amount,
                'received_amount' => (float) $booking->received_amount,
                'status' => $booking->status,
                'narration' => $booking->narration,
                'send_whatsapp' => (bool) $booking->send_whatsapp,
                'has_external_payments' => $booking->payments->contains('source', 'BUY_SELL'),
                'booked_at' => $booking->booked_at ? $booking->booked_at->toIso8601String() : null,
                'items' => $booking->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sort_order' => $item->sort_order,
                        'weight' => (float) $item->weight,
                        'rate' => (float) $item->rate,
                        'amount' => (float) $item->amount,
                    ];
                })->all(),
                'payments' => $booking->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'mode' => $payment->mode,
                        'online_method' => $payment->online_method,
                        'reference_no' => $payment->reference_no,
                        'amount' => (float) $payment->amount,
                        'source' => $payment->source,
                        'sort_order' => $payment->sort_order,
                        'paid_at' => $payment->paid_at ? $payment->paid_at->toIso8601String() : null,
                    ];
                })->all(),
            ];
        });

        return response()->json([
            'rows' => $rows,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
            'totals' => $totals,
        ]);
    }

    /**
     * Store a newly created Bhav Booking in storage.
     */
    public function store(StoreBhavBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->bhavBookingService->create($validated);

        return response()->json($result['booking'], $result['created'] ? 201 : 200);
    }

    /**
     * Display the specified Bhav Booking details.
     */
    /**
     * Display the specified Bhav Booking details including activity logs.
     */
    public function show(BhavBooking $bhavBooking): JsonResponse
    {
        $bhavBooking->load([
            'customer:id,customer_no,name,mobile,city',
            'items:id,bhav_booking_id,sort_order,weight,rate,amount',
            'payments:id,bhav_booking_id,mode,online_method,reference_no,amount,source,sort_order,paid_at',
            'logs',
        ]);
        $bhavBooking->has_external_payments = $bhavBooking->payments->contains('source', 'BUY_SELL');

        return response()->json($bhavBooking);
    }

    /**
     * Streamed CSV export of the currently filtered bookings.
     * Uses chunked database cursor to ensure zero memory exhaustion on large sets.
     */
    public function export(ListBhavBookingRequest $request): StreamedResponse
    {
        $query = $this->getFilteredQuery($request)
            ->with(['customer', 'items', 'payments'])
            ->orderByDesc('booked_at')
            ->orderByDesc('id');

        $filename = 'bhav_bookings_export_'.now()->format('Ymd_His').'.csv';

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Booking No',
                'Date',
                'Customer No',
                'Customer Name',
                'Mobile',
                'City',
                'Trade Type',
                'Metal',
                'Type',
                'Total Weight (gm)',
                'Delivered Weight (gm)',
                'Pending Weight (gm)',
                'Total Amount (₹)',
                'Given Amount (₹)',
                'Received Amount (₹)',
                'Status',
                'Payments',
                'Narration',
            ]);

            // Chunk in sets of 500 records to maintain minimal memory footprint
            $query->chunk(500, function ($bookings) use ($handle) {
                foreach ($bookings as $booking) {
                    $paymentsSummary = $booking->payments->map(function ($p) {
                        $method = $p->mode === 'ONLINE' ? " ({$p->online_method})" : '';
                        $ref = $p->reference_no ? " Ref:{$p->reference_no}" : '';

                        return "{$p->mode}{$method}: ₹{$p->amount}{$ref}";
                    })->implode('; ');

                    fputcsv($handle, [
                        $booking->booking_no,
                        $booking->booked_at ? $booking->booked_at->format('d-m-Y H:i') : '',
                        $booking->customer->customer_no ?? '',
                        $booking->customer->name ?? '',
                        $booking->customer->mobile ?? '',
                        $booking->customer->city ?? '',
                        $booking->trade_type,
                        $booking->metal,
                        $booking->entry_type === 'BHAV_CUT_LEDGER' ? 'Bhav Cut + Ledger' : 'Only Entry',
                        number_format((float) $booking->total_weight, 3, '.', ''),
                        number_format((float) $booking->delivered_weight, 3, '.', ''),
                        number_format((float) max(0, $booking->total_weight - $booking->delivered_weight), 3, '.', ''),
                        number_format((float) $booking->total_amount, 2, '.', ''),
                        number_format((float) $booking->given_amount, 2, '.', ''),
                        number_format((float) $booking->received_amount, 2, '.', ''),
                        $booking->status,
                        $paymentsSummary,
                        $booking->narration ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Log an external action (PRINTED, SHARED, SILENT_DELETED) on a booking.
     */
    public function logAction(Request $request, BhavBooking $bhavBooking): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:PRINTED,SHARED,SILENT_DELETED'],
        ]);

        $log = $this->bhavBookingService->logAction($bhavBooking, $validated['action']);

        return response()->json([
            'message' => 'Action logged successfully',
            'log' => $log,
        ]);
    }

    /**
     * Update the specified Bhav Booking in storage.
     * Allowed only if status = PENDING and delivered_weight = 0 and given_amount = 0 and received_amount = 0.
     */
    public function update(UpdateBhavBookingRequest $request, BhavBooking $bhavBooking): JsonResponse
    {
        if (! $this->bhavBookingService->canModify($bhavBooking)) {
            return response()->json([
                'message' => 'This booking has delivery/payment and cannot be edited',
            ], 422);
        }

        $validated = $request->validated();
        $booking = $this->bhavBookingService->update($bhavBooking, $validated);

        return response()->json($booking);
    }

    /**
     * Remove the specified Bhav Booking from storage (soft delete).
     * Allowed only if status = PENDING and delivered_weight = 0 and given_amount = 0 and received_amount = 0.
     */
    public function destroy(BhavBooking $bhavBooking): JsonResponse
    {
        if (! $this->bhavBookingService->canModify($bhavBooking)) {
            return response()->json([
                'message' => 'This booking has delivery/payment and cannot be edited',
            ], 422);
        }

        $this->bhavBookingService->delete($bhavBooking);

        return response()->json([
            'message' => 'Booking deleted successfully.',
        ]);
    }

    /**
     * Print voucher view for a single booking.
     */
    public function print(BhavBooking $bhavBooking)
    {
        $booking = $bhavBooking->load(['customer', 'items', 'payments']);

        return view('buy-sell.bhav-booking-print', compact('booking'));
    }
}
