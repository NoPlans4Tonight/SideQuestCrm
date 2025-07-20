<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerCrudOrchestratorService
{
    public function __construct(
        private CustomerServiceInterface $customerService
    ) {}

    /**
     * Create a new customer
     */
    public function createCustomer(Request $request, int $tenantId, int $userId): array
    {
        try {
            $customer = $this->customerService->createCustomer(
                $request->all(),
                $tenantId,
                $userId
            );

            return [
                'message' => 'Customer created successfully',
                'data' => $customer
            ];
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Update an existing customer
     */
    public function updateCustomer(int $customerId, Request $request): array
    {
        $customer = $this->findCustomerOrFail($customerId);

        try {
            $updatedCustomer = $this->customerService->updateCustomer($customer->id, $request->all());

            return [
                'message' => 'Customer updated successfully',
                'data' => new CustomerResource($updatedCustomer)
            ];
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Delete a customer
     */
    public function deleteCustomer(int $customerId): array
    {
        $customer = $this->findCustomerOrFail($customerId);

        $this->customerService->deleteCustomer($customer->id);

        return [
            'message' => 'Customer deleted successfully'
        ];
    }

    /**
     * Find customer or throw 404
     */
    private function findCustomerOrFail(int $customerId): Customer
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            abort(404, 'Customer not found');
        }

        return $customer;
    }
}
