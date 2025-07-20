<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerListingOrchestratorService;
use App\Services\CustomerCrudOrchestratorService;
use App\Services\CustomerDetailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerListingOrchestratorService $listingOrchestrator,
        private CustomerCrudOrchestratorService $crudOrchestrator,
        private CustomerDetailService $detailService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $result = $this->listingOrchestrator->getCustomersForRequest($request, $user->tenant_id);

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $result = $this->crudOrchestrator->createCustomer($request, $user->tenant_id, $user->id);

            return response()->json($result, 201);
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
        try {
            $result = $this->crudOrchestrator->updateCustomer($id, $request);

            return response()->json($result);
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
        $result = $this->crudOrchestrator->deleteCustomer($id);

        return response()->json($result);
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
