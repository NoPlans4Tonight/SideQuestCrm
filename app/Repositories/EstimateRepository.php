<?php

namespace App\Repositories;

use App\Contracts\Repositories\EstimateRepositoryInterface;
use App\Models\Estimate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EstimateRepository implements EstimateRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Estimate
    {
        return Estimate::where('tenant_id', $tenantId)
            ->with(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service'])
            ->find($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)->get();
    }

    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Estimate::where('tenant_id', $tenantId)
            ->with(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Estimate
    {
        return Estimate::create($data);
    }

    public function update(Estimate $estimate, array $data): Estimate
    {
        $estimate->update($data);
        return $estimate->fresh();
    }

    public function delete(Estimate $estimate): bool
    {
        return $estimate->delete();
    }

    public function findByEstimateNumber(string $estimateNumber, int $tenantId): ?Estimate
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('estimate_number', $estimateNumber)
            ->first();
    }

    public function searchByTenant(int $tenantId, string $query): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('estimate_number', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['customer', 'assignedUser'])
            ->get();
    }

    public function getByStatus(int $tenantId, string $status): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByCustomer(int $tenantId, int $customerId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->with(['customer', 'assignedUser', 'estimateItems.service'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByAssignedUser(int $tenantId, int $userId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->with(['customer', 'estimateItems.service'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExpiredEstimates(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', 'expired')
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingEstimates(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSentEstimates(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', 'sent')
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAcceptedEstimates(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', 'accepted')
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRejectedEstimates(int $tenantId): Collection
    {
        return Estimate::where('tenant_id', $tenantId)
            ->where('status', 'rejected')
            ->with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
