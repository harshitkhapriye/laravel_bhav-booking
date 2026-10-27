<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyRequest;
use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    /**
     * Search customers by name, mobile, or customer number.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        // If query is empty, return an empty array
        if ($q === '') {
            return response()->json([]);
        }

        // Search customers where name matches (contains) OR mobile/customer_no starts with query (leveraging indexes)
        $customers = Customer::query()
            ->select(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('mobile', 'LIKE', "{$q}%")
                    ->orWhere('customer_no', 'LIKE', "{$q}%");
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    /**
     * Get recent customers ordered by their latest Bhav Booking.
     * Fills remaining slots up to 8 with newest customers by id desc without duplicates.
     * Selects only required columns.
     */
    public function recent(): JsonResponse
    {
        // 1. Get up to 8 distinct customer IDs ordered by their latest booking
        $recentCustomerIds = BhavBooking::query()
            ->select('customer_id')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc(DB::raw('MAX(booked_at)'))
            ->limit(8)
            ->pluck('customer_id')
            ->toArray();

        // 2. Fetch the corresponding Customer models, preserving the order of recent bookings
        $recentCustomers = collect();
        if (! empty($recentCustomerIds)) {
            $recentCustomers = Customer::query()
                ->select(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image'])
                ->whereIn('id', $recentCustomerIds)
                ->get()
                ->sortBy(function ($customer) use ($recentCustomerIds) {
                    return array_search($customer->id, $recentCustomerIds);
                })
                ->values();
        }

        // 3. If fewer than 8, fill the rest with the newest customers (by id desc) without duplicates
        $count = $recentCustomers->count();
        if ($count < 8) {
            $remaining = 8 - $count;
            $existingIds = $recentCustomers->pluck('id')->toArray();

            $newestCustomers = Customer::query()
                ->select(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image'])
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('id')
                ->limit($remaining)
                ->get();

            $recentCustomers = $recentCustomers->concat($newestCustomers);
        }

        return response()->json($recentCustomers->values());
    }

    /**
     * Store a newly created party (Customer or Supplier).
     * Accepts multipart/form-data for image uploads.
     *
     * @param  Request  $request
     */
    public function store(StorePartyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $isFamilySubProfile = filter_var($request->input('is_family_sub_profile'), FILTER_VALIDATE_BOOLEAN);

        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            $profileImagePath = $request->file('profile_image')->store('customers', 'public');
        }

        // Handle shop logo upload
        $shopLogoPath = null;
        if ($request->hasFile('shop_logo') && $request->file('shop_logo')->isValid()) {
            $shopLogoPath = $request->file('shop_logo')->store('customers', 'public');
        }

        // City fallback: city if provided, otherwise district
        $city = ! empty($validated['city']) ? $validated['city'] : ($validated['district'] ?? null);

        $customer = null;
        $maxRetries = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $customer = DB::transaction(function () use ($validated, $isFamilySubProfile, $city, $profileImagePath, $shopLogoPath) {
                    // Mobile duplicate race: before insert, re-check uniqueness inside transaction
                    $allowSameMobileAsParent = false;
                    if ($isFamilySubProfile && ! empty($validated['parent_customer_id'] ?? null)) {
                        $parent = Customer::find($validated['parent_customer_id']);
                        if ($parent && (string) $parent->mobile === (string) $validated['mobile']) {
                            $allowSameMobileAsParent = true;
                        }
                    }

                    if (! $allowSameMobileAsParent) {
                        $exists = Customer::where('mobile', $validated['mobile'])->lockForUpdate()->exists();
                        if ($exists) {
                            throw ValidationException::withMessages([
                                'mobile' => ['The mobile has already been taken.'],
                            ]);
                        }
                    }

                    // Customer number race: generate inside transaction with lock
                    $customerNo = Customer::generateNextCustomerNo(true);

                    return Customer::create([
                        'customer_no' => $customerNo,
                        'name' => $validated['name'],
                        'party_type' => $validated['party_type'] ?? 'CUSTOMER',
                        'parent_customer_id' => $isFamilySubProfile ? ($validated['parent_customer_id'] ?? null) : null,
                        'mobile' => $validated['mobile'],
                        'city' => $city,
                        'email' => $validated['email'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'profile_image' => $profileImagePath,
                        'birthday' => $validated['birthday'] ?? null,
                        'anniversary' => $validated['anniversary'] ?? null,
                        'aadhaar_no' => $validated['aadhaar_no'] ?? null,
                        'pan_no' => $validated['pan_no'] ?? null,
                        'gst_no' => $validated['gst_no'] ?? null,
                        'lic_no' => $validated['lic_no'] ?? null,
                        'reg_no' => $validated['reg_no'] ?? null,
                        'logo_title' => $validated['logo_title'] ?? null,
                        'shop_name' => $validated['shop_name'] ?? null,
                        'state' => $validated['state'] ?? null,
                        'district' => $validated['district'] ?? null,
                        'area' => $validated['area'] ?? null,
                        'shop_address' => $validated['shop_address'] ?? null,
                        'shop_logo' => $shopLogoPath,
                    ]);
                });
                break;
            } catch (QueryException $e) {
                if ($attempt < $maxRetries && (($e->errorInfo[1] ?? 0) === 1062 || $e->getCode() === '23000')) {
                    continue;
                }
                throw $e;
            }
        }

        return response()->json([
            'id' => $customer->id,
            'customer_no' => $customer->customer_no,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'city' => $customer->city,
            'party_type' => $customer->party_type,
            'profile_image_url' => $customer->profile_image_url,
        ], 201);
    }
}
