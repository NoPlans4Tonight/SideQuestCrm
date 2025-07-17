<?php

namespace App\Repositories;

use App\Contracts\Repositories\ServiceRepositoryInterface;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Service::where('tenant_id', $tenantId)->get();
    }

    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Service::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
        return $service->delete();
    }

    public function searchByTenant(int $tenantId, string $query): Collection
    {
        return Service::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->get();
    }

    public function getByCategory(int $tenantId, string $category): Collection
    {
        return Service::where('tenant_id', $tenantId)
            ->where('category', $category)
            ->get();
    }

    public function getActiveServices(int $tenantId): Collection
    {
        return Service::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    public function getByCreatedBy(int $tenantId, int $userId): Collection
    {
        return Service::where('tenant_id', $tenantId)
            ->where('created_by', $userId)
            ->get();
    }
}
