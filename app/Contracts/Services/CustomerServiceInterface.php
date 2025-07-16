<?php

namespace App\Contracts\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerServiceInterface
{
    public function getCustomers(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function getCustomer(int $id): ?Customer;
    public function createCustomer(array $data, int $tenantId, int $userId): Customer;
    public function updateCustomer(int $id, array $data): Customer;
    public function deleteCustomer(int $id): bool;
    public function searchCustomers(int $tenantId, string $query): Collection;
    public function getCustomersByStatus(int $tenantId, string $status): Collection;
    public function getCustomersByAssignedUser(int $tenantId, int $userId): Collection;
    public function validateCustomerData(array $data): array;
}
