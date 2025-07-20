<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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

    public function paginateByTenant(int $tenantId, int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $query = Customer::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc');

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->paginate($perPage);
    }

    public function getCustomersWithSummary(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::where('tenant_id', $tenantId)
            ->with([
                'assignedUser:id,name,email',
                'createdBy:id,name,email',
                'appointments:id,customer_id,status,start_time,total_cost,service_id',
                'appointments.service:id,name,description,base_price',
                'estimates:id,customer_id,status,total_amount,subtotal,tax_amount,discount_amount,valid_until'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        // Validate email uniqueness at repository level
        if (isset($data['email']) && $data['email']) {
            $this->validateEmailUniqueness($data['email'], $data['tenant_id'] ?? null);
        }

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        // Validate email uniqueness at repository level
        if (isset($data['email']) && $data['email']) {
            $this->validateEmailUniqueness($data['email'], $customer->tenant_id, $customer->id);
        }

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

    /**
     * Validate email uniqueness within tenant
     */
    private function validateEmailUniqueness(string $email, ?int $tenantId, ?int $excludeCustomerId = null): void
    {
        $query = Customer::where('email', $email);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($excludeCustomerId) {
            $query->where('id', '!=', $excludeCustomerId);
        }

        if ($query->exists()) {
            $validator = Validator::make(['email' => $email], [
                'email' => 'unique:customers,email'
            ]);

            throw new ValidationException($validator);
        }
    }
}
