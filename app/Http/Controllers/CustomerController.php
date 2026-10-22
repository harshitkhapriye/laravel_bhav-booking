<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->get(['id', 'customer_no', 'name', 'mobile', 'city']);

        return response()->json($customers);
    }

    /**
     * Store a newly created customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request data
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'mobile' => 'nullable|digits:10|unique:customers,mobile',
            'city'   => 'nullable|string|max:50',
        ]);

        // Auto-generate customer_no
        $customerNo = Customer::generateNextCustomerNo();

        // Create the new customer record
        $customer = Customer::create([
            'customer_no' => $customerNo,
            'name'        => $validated['name'],
            'mobile'      => !empty($validated['mobile']) ? $validated['mobile'] : null,
            'city'        => !empty($validated['city']) ? $validated['city'] : null,
        ]);

        return response()->json($customer, 201);
    }
}
