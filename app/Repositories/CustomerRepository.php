<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Customer::where('tenant_id', $tenantId)->get();
    }

    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::where('tenant_id', $tenantId)
            ->with(['assignedUser', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    public function findByEmail(string $email, int $tenantId): ?Customer
    {
        return Customer::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->first();
    }

    public function searchByTenant(int $tenantId, string $query): Collection
    {
        return Customer::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->get();
    }

    public function getByStatus(int $tenantId, string $status): Collection
    {
        return Customer::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get();
    }

    public function getByAssignedUser(int $tenantId, int $userId): Collection
    {
        return Customer::where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->get();
    }
}
