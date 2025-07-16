<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\CustomerServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerServiceInterface $customerService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->get('per_page', 15);
        $customers = $this->customerService->getCustomers(
            Auth::user()->tenant_id,
            $perPage
        );

        return CustomerCollection::make($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->createCustomer(
            $request->validated(),
            Auth::user()->tenant_id,
            Auth::id()
        );

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => CustomerResource::make($customer)
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);

        if (!$customer || $customer->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json([
            'data' => CustomerResource::make($customer)
        ]);
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);

        if (!$customer || $customer->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $updatedCustomer = $this->customerService->updateCustomer($id, $request->validated());

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => CustomerResource::make($updatedCustomer)
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);

        if (!$customer || $customer->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $this->customerService->deleteCustomer($id);

        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return CustomerCollection::make(collect());
        }

        $customers = $this->customerService->searchCustomers(
            Auth::user()->tenant_id,
            $query
        );

        return CustomerCollection::make($customers);
    }

    public function byStatus(Request $request): AnonymousResourceCollection
    {
        $status = $request->get('status', 'active');

        $customers = $this->customerService->getCustomersByStatus(
            Auth::user()->tenant_id,
            $status
        );

        return CustomerCollection::make($customers);
    }

    public function byAssignedUser(Request $request): AnonymousResourceCollection
    {
        $userId = $request->get('user_id', Auth::id());

        $customers = $this->customerService->getCustomersByAssignedUser(
            Auth::user()->tenant_id,
            $userId
        );

        return CustomerCollection::make($customers);
    }
}
