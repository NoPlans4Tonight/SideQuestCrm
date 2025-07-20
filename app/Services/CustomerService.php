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

    public function getCustomers(int $tenantId, int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        return $this->customerRepository->paginateByTenant($tenantId, $perPage, $with);
    }

    public function getCustomersWithSummary(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->getCustomersWithSummary($tenantId, $perPage);
    }

    public function getCustomer(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function createCustomer(array $data, int $tenantId, int $userId): Customer
    {
        // Validate business rules (no database dependency)
        $validatedData = $this->validateBusinessRules($data);

        // Let repository handle database-specific validation (email uniqueness)
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

        // Validate business rules only
        $validatedData = $this->validateBusinessRules($data, true);

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

    /**
     * Validate business rules without database dependencies
     */
    private function validateBusinessRules(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'first_name' => $isUpdate ? 'sometimes|nullable|string|max:255' : 'required|string|max:255',
            'last_name' => $isUpdate ? 'sometimes|nullable|string|max:255' : 'required|string|max:255',
            'email' => 'nullable|email|max:255', // No uniqueness check here
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

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Legacy method for backward compatibility - delegates to repository
     */
    public function validateCustomerData(array $data, ?int $customerId = null): array
    {
        // This method still exists for backward compatibility
        // but database validation should now be handled by repository
        $businessRules = $this->validateBusinessRules($data, $customerId !== null);

        // For full validation including uniqueness, this should be called at repository level
        return $businessRules;
    }
}
