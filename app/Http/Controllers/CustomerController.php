<?php

namespace App\Http\Controllers;

use App\Models\BhavBooking;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Search customers by name, mobile, or customer number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        // If query is empty, return an empty array
        if ($q === '') {
            return response()->json([]);
        }

        // Search customers where name OR mobile OR customer_no matches, limit to 10 ordered by name
        $customers = Customer::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('mobile', 'LIKE', "%{$q}%")
                      ->orWhere('customer_no', 'LIKE', "%{$q}%");
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image']);

        return response()->json($customers);
    }

    /**
     * Get recent customers ordered by their latest Bhav Booking.
     * Fills remaining slots up to 8 with newest customers by id desc without duplicates.
     *
     * @return JsonResponse
     */
    public function recent(): JsonResponse
    {
        // 1. Get up to 8 distinct customer IDs ordered by their latest booking
        $recentCustomerIds = BhavBooking::query()
            ->select('customer_id', DB::raw('MAX(booked_at) as latest_booking'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('latest_booking')
            ->limit(8)
            ->pluck('customer_id')
            ->toArray();

        // 2. Fetch the corresponding Customer models, preserving the order of recent bookings
        $recentCustomers = collect();
        if (!empty($recentCustomerIds)) {
            $recentCustomers = Customer::whereIn('id', $recentCustomerIds)
                ->get(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image'])
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
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('id')
                ->limit($remaining)
                ->get(['id', 'customer_no', 'name', 'mobile', 'city', 'party_type', 'profile_image']);

            $recentCustomers = $recentCustomers->concat($newestCustomers);
        }

        return response()->json($recentCustomers->values());
    }

    /**
     * Store a newly created party (Customer or Supplier).
     * Accepts multipart/form-data for image uploads.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Normalize uppercase fields if provided
        if ($request->has('pan_no') && is_string($request->input('pan_no'))) {
            $pan = strtoupper(trim($request->input('pan_no')));
            $request->merge(['pan_no' => $pan !== '' ? $pan : null]);
        }
        if ($request->has('gst_no') && is_string($request->input('gst_no'))) {
            $gst = strtoupper(trim($request->input('gst_no')));
            $request->merge(['gst_no' => $gst !== '' ? $gst : null]);
        }

        // Determine if this is a family sub-profile
        $isFamilySubProfile = filter_var($request->input('is_family_sub_profile'), FILTER_VALIDATE_BOOLEAN);
        $request->merge(['is_family_sub_profile' => $isFamilySubProfile]);

        // Base mobile validation rules
        $mobileRules = ['required', 'digits:10'];

        // Mobile unique rule exception:
        // When is_family_sub_profile is true and mobile equals the selected parent customer's mobile
        $parentId = $request->input('parent_customer_id');
        $allowSameMobileAsParent = false;

        if ($isFamilySubProfile && !empty($parentId)) {
            $parent = Customer::find($parentId);
            if ($parent && (string) $parent->mobile === (string) $request->input('mobile')) {
                $allowSameMobileAsParent = true;
            }
        }

        if (!$allowSameMobileAsParent) {
            $mobileRules[] = 'unique:customers,mobile';
        }

        // Validate all incoming party fields
        $validated = $request->validate([
            'name'                  => 'required|string|max:100',
            'mobile'                => $mobileRules,
            'party_type'            => 'required|in:CUSTOMER,SUPPLIER',
            'is_family_sub_profile' => 'boolean',
            'parent_customer_id'    => 'required_if:is_family_sub_profile,true|nullable|exists:customers,id',
            'email'                 => 'nullable|email|max:150',
            'address'               => 'nullable|string|max:500',
            'birthday'              => 'nullable|date|before_or_equal:today',
            'anniversary'           => 'nullable|date|before_or_equal:today',
            'aadhaar_no'            => 'nullable|digits:12',
            'pan_no'                => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'gst_no'                => ['nullable', 'regex:/^[0-9]{2}[A-Z0-9]{13}$/'],
            'lic_no'                => 'nullable|string|max:100',
            'reg_no'                => 'nullable|string|max:100',
            'logo_title'            => 'nullable|string|max:100',
            'shop_name'             => 'nullable|string|max:100',
            'area'                  => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:60',
            'district'              => 'nullable|string|max:60',
            'shop_address'          => 'nullable|string|max:500',
            'profile_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'shop_logo'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'city'                  => 'nullable|string|max:50',
        ], [
            'name.required'                  => 'Party name is required',
            'name.max'                       => 'Party name may not be greater than 100 characters',
            'mobile.required'                => 'Mobile number is required',
            'mobile.digits'                  => 'Mobile number must be 10 digits',
            'mobile.unique'                  => 'This mobile number is already registered',
            'party_type.required'            => 'Party type is required',
            'party_type.in'                  => 'Party type must be Customer or Supplier',
            'parent_customer_id.required_if' => 'Please select the main customer for this family sub-profile',
            'parent_customer_id.exists'      => 'Selected main customer was not found',
            'email.email'                    => 'Please enter a valid email address',
            'email.max'                      => 'Email may not be greater than 150 characters',
            'address.max'                    => 'Party address may not be greater than 500 characters',
            'birthday.date'                  => 'Please enter a valid birthday date',
            'birthday.before_or_equal'       => 'Birthday cannot be a future date',
            'anniversary.date'               => 'Please enter a valid anniversary date',
            'anniversary.before_or_equal'    => 'Anniversary cannot be a future date',
            'aadhaar_no.digits'              => 'Aadhaar number must be 12 digits',
            'pan_no.regex'                   => 'Invalid PAN format (e.g. ABCDE1234F)',
            'gst_no.regex'                   => 'Invalid GST number format (15 characters, e.g. 22AAAAA0000A1Z5)',
            'lic_no.max'                     => 'License number may not be greater than 100 characters',
            'reg_no.max'                     => 'Registration number may not be greater than 100 characters',
            'logo_title.max'                 => 'Logo title may not be greater than 100 characters',
            'shop_name.max'                  => 'Shop / Business name may not be greater than 100 characters',
            'area.max'                       => 'Area / Landmark may not be greater than 100 characters',
            'state.max'                      => 'State may not be greater than 60 characters',
            'district.max'                   => 'District may not be greater than 60 characters',
            'shop_address.max'               => 'Shop address may not be greater than 500 characters',
            'profile_image.image'            => 'Profile image must be an image',
            'profile_image.mimes'            => 'Profile image must be a JPG, JPEG, PNG, or WEBP file',
            'profile_image.max'              => 'Profile image may not be larger than 2MB',
            'shop_logo.image'                => 'Shop logo must be an image',
            'shop_logo.mimes'                => 'Shop logo must be a JPG, JPEG, PNG, or WEBP file',
            'shop_logo.max'                  => 'Shop logo may not be larger than 2MB',
        ]);

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

        // Auto-generate sequential customer number
        $customerNo = Customer::generateNextCustomerNo();

        // City fallback: city if provided, otherwise district
        $city = !empty($validated['city']) ? $validated['city'] : ($validated['district'] ?? null);

        // Create the party record
        $customer = Customer::create([
            'customer_no'        => $customerNo,
            'name'               => $validated['name'],
            'party_type'         => $validated['party_type'] ?? 'CUSTOMER',
            'parent_customer_id' => $isFamilySubProfile ? ($validated['parent_customer_id'] ?? null) : null,
            'mobile'             => $validated['mobile'],
            'city'               => $city,
            'email'              => $validated['email'] ?? null,
            'address'            => $validated['address'] ?? null,
            'profile_image'      => $profileImagePath,
            'birthday'           => $validated['birthday'] ?? null,
            'anniversary'        => $validated['anniversary'] ?? null,
            'aadhaar_no'         => $validated['aadhaar_no'] ?? null,
            'pan_no'             => $validated['pan_no'] ?? null,
            'gst_no'             => $validated['gst_no'] ?? null,
            'lic_no'             => $validated['lic_no'] ?? null,
            'reg_no'             => $validated['reg_no'] ?? null,
            'logo_title'         => $validated['logo_title'] ?? null,
            'shop_name'          => $validated['shop_name'] ?? null,
            'state'              => $validated['state'] ?? null,
            'district'           => $validated['district'] ?? null,
            'area'               => $validated['area'] ?? null,
            'shop_address'       => $validated['shop_address'] ?? null,
            'shop_logo'          => $shopLogoPath,
        ]);

        return response()->json([
            'id'                => $customer->id,
            'customer_no'       => $customer->customer_no,
            'name'              => $customer->name,
            'mobile'            => $customer->mobile,
            'city'              => $customer->city,
            'party_type'        => $customer->party_type,
            'profile_image_url' => $customer->profile_image_url,
        ], 201);
    }
}
