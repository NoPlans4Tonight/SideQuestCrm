<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;
    public function findByTenant(int $tenantId): Collection;
    public function paginateByTenant(int $tenantId, int $perPage = 15, array $with = []): LengthAwarePaginator;
    public function getCustomersWithSummary(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Customer;
    public function update(Customer $customer, array $data): Customer;
    public function delete(Customer $customer): bool;
    public function findByEmail(string $email, int $tenantId): ?Customer;
    public function searchByTenant(int $tenantId, string $query): Collection;
    public function getByStatus(int $tenantId, string $status): Collection;
    public function getByAssignedUser(int $tenantId, int $userId): Collection;
}
