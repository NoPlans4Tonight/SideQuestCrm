<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Services\CustomerServiceInterface;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerService implements CustomerServiceInterface
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomers(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginateByTenant($tenantId, $perPage);
    }

    public function getCustomer(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function createCustomer(array $data, int $tenantId, int $userId): Customer
    {
        $validatedData = $this->validateCustomerData($data);

        $customerData = array_merge($validatedData, [
            'tenant_id' => $tenantId,
            'created_by' => $userId,
        ]);

        return $this->customerRepository->create($customerData);
    }

    public function updateCustomer(int $id, array $data): Customer
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        $validatedData = $this->validateCustomerData($data, $id);

        return $this->customerRepository->update($customer, $validatedData);
    }

    public function deleteCustomer(int $id): bool
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        return $this->customerRepository->delete($customer);
    }

    public function searchCustomers(int $tenantId, string $query): Collection
    {
        return $this->customerRepository->searchByTenant($tenantId, $query);
    }

    public function getCustomersByStatus(int $tenantId, string $status): Collection
    {
        return $this->customerRepository->getByStatus($tenantId, $status);
    }

    public function getCustomersByAssignedUser(int $tenantId, int $userId): Collection
    {
        return $this->customerRepository->getByAssignedUser($tenantId, $userId);
    }

    public function validateCustomerData(array $data, ?int $customerId = null): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,prospect',
            'source' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ];

        // Add unique email validation if customer ID is provided (for updates)
        if ($customerId) {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email,' . $customerId;
        } else {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
