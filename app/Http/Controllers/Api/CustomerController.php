<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\CustomerListingService;
use App\Services\CustomerDetailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerListingService $listingService,
        private CustomerDetailService $detailService
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Add caching for frequently accessed data
        $cacheKey = "customers_{$user->tenant_id}_" . md5($request->fullUrl());
        $cacheDuration = 300; // 5 minutes

        // Don't cache if there are filters or specific parameters
        if ($request->has('filter') || $request->has('search') || $request->get('per_page', 15) != 15) {
            $result = $this->listingService->getCustomers($request, $user->tenant_id);
        } else {
            $result = cache()->remember($cacheKey, $cacheDuration, function () use ($request, $user) {
                return $this->listingService->getCustomers($request, $user->tenant_id);
            });
        }

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $customer = $this->customerService->createCustomer(
                $request->all(),
                $user->tenant_id,
                $user->id
            );

            return response()->json([
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        $user = auth()->user();
        $result = $this->detailService->getCustomer($id, $user->tenant_id, $request);

        if (!$result) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        try {
            $updatedCustomer = $this->customerService->updateCustomer($customer->id, $request->all());
            return response()->json([
                'message' => 'Customer updated successfully',
                'data' => new CustomerResource($updatedCustomer)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        $this->customerService->deleteCustomer($customer->id);
        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Get customer summary with related data
     */
    public function summary($id)
    {
        $user = auth()->user();
        $result = $this->detailService->getCustomerSummary($id, $user->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($result);
    }


}
