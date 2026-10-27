<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePartyRequest;
use App\Models\Customer;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartyController extends Controller
{
    /**
     * Display the Party Master index view.
     */
    public function index(): View
    {
        return view('parties.index');
    }

    /**
     * Get a paginated list of parties with search and filter capabilities.
     */
    public function list(Request $request): JsonResponse
    {
        $query = Customer::query();

        // 1. Text search across name, mobile, customer_no, and shop_name
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('mobile', 'LIKE', "%{$q}%")
                    ->orWhere('customer_no', 'LIKE', "%{$q}%")
                    ->orWhere('shop_name', 'LIKE', "%{$q}%");
            });
        }

        // 2. Party type filter (ALL, CUSTOMER, SUPPLIER)
        if ($request->filled('party_type')) {
            $type = strtoupper(trim($request->input('party_type')));
            if (in_array($type, ['CUSTOMER', 'SUPPLIER'], true)) {
                $query->where('party_type', $type);
            }
        }

        // 3. Eager load parent relation and with counts for family members and bhav bookings
        $query->with(['parent:id,name'])
            ->withCount([
                'familyMembers as family_count',
                'bhavBookings as bookings_count',
            ]);

        // 4. Order by name ascending
        $query->orderBy('name', 'asc');

        // 5. Pagination per_page (10, 50, 100)
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 50, 100], true)) {
            $perPage = 10;
        }

        $paginator = $query->paginate($perPage);

        // 6. Map rows (never expose raw aadhaar_no or pan_no)
        $rows = collect($paginator->items())->map(function (Customer $party) {
            return [
                'id' => $party->id,
                'customer_no' => $party->customer_no,
                'name' => $party->name,
                'party_type' => $party->party_type,
                'mobile' => $party->mobile,
                'email' => $party->email,
                'district' => $party->district,
                'state' => $party->state,
                'shop_name' => $party->shop_name,
                'profile_image_url' => $party->profile_image_url,
                'parent' => $party->parent ? [
                    'id' => $party->parent->id,
                    'name' => $party->parent->name,
                ] : null,
                'family_count' => (int) ($party->family_count ?? 0),
                'bookings_count' => (int) ($party->bookings_count ?? 0),
            ];
        });

        return response()->json([
            'rows' => $rows,
            'data' => $rows,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

    /**
     * Display the specified party details for view and edit.
     * Masked Aadhaar and PAN are returned; raw numbers are never exposed.
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load([
            'parent:id,customer_no,name,mobile',
            'familyMembers:id,customer_no,name,mobile,party_type',
            'bhavBookings' => function ($query) {
                $query->orderByDesc('booked_at')->orderByDesc('id')->limit(5);
            },
        ]);

        $data = [
            'id' => $customer->id,
            'customer_no' => $customer->customer_no,
            'name' => $customer->name,
            'party_type' => $customer->party_type,
            'parent_customer_id' => $customer->parent_customer_id,
            'parent' => $customer->parent ? [
                'id' => $customer->parent->id,
                'customer_no' => $customer->parent->customer_no,
                'name' => $customer->parent->name,
                'mobile' => $customer->parent->mobile,
            ] : null,
            'mobile' => $customer->mobile,
            'city' => $customer->city,
            'email' => $customer->email,
            'address' => $customer->address,
            'profile_image' => $customer->profile_image,
            'profile_image_url' => $customer->profile_image_url,
            'birthday' => $customer->birthday ? $customer->birthday->format('Y-m-d') : null,
            'anniversary' => $customer->anniversary ? $customer->anniversary->format('Y-m-d') : null,
            'aadhaar_no_masked' => Format::maskAadhaar($customer->aadhaar_no),
            'pan_no_masked' => Format::maskPan($customer->pan_no),
            'has_aadhaar' => ! empty($customer->aadhaar_no),
            'has_pan' => ! empty($customer->pan_no),
            'gst_no' => $customer->gst_no,
            'lic_no' => $customer->lic_no,
            'reg_no' => $customer->reg_no,
            'logo_title' => $customer->logo_title,
            'shop_name' => $customer->shop_name,
            'state' => $customer->state,
            'district' => $customer->district,
            'area' => $customer->area,
            'shop_address' => $customer->shop_address,
            'shop_logo' => $customer->shop_logo,
            'shop_logo_url' => $customer->shop_logo_url,
            'family_members' => $customer->familyMembers->map(function ($member) {
                return [
                    'id' => $member->id,
                    'customer_no' => $member->customer_no,
                    'name' => $member->name,
                    'mobile' => $member->mobile,
                    'party_type' => $member->party_type,
                ];
            }),
            'latest_bookings' => $customer->bhavBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_no' => $booking->booking_no,
                    'booked_at' => $booking->booked_at ? $booking->booked_at->format('d-m-Y h:i A') : '',
                    'trade_type' => $booking->trade_type,
                    'metal' => $booking->metal,
                    'total_weight' => number_format((float) $booking->total_weight, 3),
                    'total_amount' => Format::indianMoney($booking->total_amount),
                    'status' => $booking->status,
                ];
            }),
        ];

        return response()->json($data);
    }

    /**
     * Update the specified party in storage.
     * Supports multipart/form-data via POST with _method=PUT.
     */
    public function update(UpdatePartyRequest $request, Customer $customer): JsonResponse
    {
        $validated = $request->validated();
        $isFamilySubProfile = filter_var($request->input('is_family_sub_profile'), FILTER_VALIDATE_BOOLEAN);

        // 1. Handle profile image removal or replacement
        $removeProfileImage = filter_var($request->input('remove_profile_image'), FILTER_VALIDATE_BOOLEAN);
        if ($removeProfileImage && ! empty($customer->profile_image)) {
            Storage::disk('public')->delete($customer->profile_image);
            $customer->profile_image = null;
        }

        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            if (! empty($customer->profile_image)) {
                Storage::disk('public')->delete($customer->profile_image);
            }
            $customer->profile_image = $request->file('profile_image')->store('customers', 'public');
        }

        // 2. Handle shop logo removal or replacement
        $removeShopLogo = filter_var($request->input('remove_shop_logo'), FILTER_VALIDATE_BOOLEAN);
        if ($removeShopLogo && ! empty($customer->shop_logo)) {
            Storage::disk('public')->delete($customer->shop_logo);
            $customer->shop_logo = null;
        }

        if ($request->hasFile('shop_logo') && $request->file('shop_logo')->isValid()) {
            if (! empty($customer->shop_logo)) {
                Storage::disk('public')->delete($customer->shop_logo);
            }
            $customer->shop_logo = $request->file('shop_logo')->store('customers', 'public');
        }

        // 3. Aadhaar / PAN: if left empty keep old value, if typed validate and replace
        if ($request->filled('aadhaar_no')) {
            $customer->aadhaar_no = $request->input('aadhaar_no');
        }
        if ($request->filled('pan_no')) {
            $customer->pan_no = $request->input('pan_no');
        }

        // 4. City fallback: city if provided, otherwise district
        $city = ! empty($validated['city']) ? $validated['city'] : ($validated['district'] ?? null);

        // 5. Update the remaining attributes
        $customer->name = $validated['name'];
        $customer->party_type = $validated['party_type'] ?? 'CUSTOMER';
        $customer->parent_customer_id = $isFamilySubProfile ? ($validated['parent_customer_id'] ?? null) : null;
        $customer->mobile = $validated['mobile'];
        $customer->city = $city;
        $customer->email = $validated['email'] ?? null;
        $customer->address = $validated['address'] ?? null;
        $customer->birthday = $validated['birthday'] ?? null;
        $customer->anniversary = $validated['anniversary'] ?? null;
        $customer->gst_no = $validated['gst_no'] ?? null;
        $customer->lic_no = $validated['lic_no'] ?? null;
        $customer->reg_no = $validated['reg_no'] ?? null;
        $customer->logo_title = $validated['logo_title'] ?? null;
        $customer->shop_name = $validated['shop_name'] ?? null;
        $customer->state = $validated['state'] ?? null;
        $customer->district = $validated['district'] ?? null;
        $customer->area = $validated['area'] ?? null;
        $customer->shop_address = $validated['shop_address'] ?? null;

        $customer->save();

        return response()->json([
            'id' => $customer->id,
            'customer_no' => $customer->customer_no,
            'name' => $customer->name,
            'party_type' => $customer->party_type,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'district' => $customer->district,
            'state' => $customer->state,
            'shop_name' => $customer->shop_name,
            'profile_image_url' => $customer->profile_image_url,
            'message' => 'Party updated successfully.',
        ]);
    }

    /**
     * Delete the specified party from storage.
     * Only allowed if the party has no bhav bookings and no family members.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $hasBookings = $customer->bhavBookings()->exists();
        $hasFamily = $customer->familyMembers()->exists();

        if ($hasBookings || $hasFamily) {
            return response()->json([
                'message' => 'This party has bookings or family members and cannot be deleted.',
            ], 422);
        }

        // Delete associated uploaded files from public storage
        if (! empty($customer->profile_image)) {
            Storage::disk('public')->delete($customer->profile_image);
        }
        if (! empty($customer->shop_logo)) {
            Storage::disk('public')->delete($customer->shop_logo);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Party deleted successfully.',
        ]);
    }
}
