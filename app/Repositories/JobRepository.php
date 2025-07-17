<?php

namespace App\Repositories;

use App\Contracts\Repositories\JobRepositoryInterface;
use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobRepository implements JobRepositoryInterface
{
    public function findById(int $id): ?Job
    {
        return Job::find($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Job::where('tenant_id', $tenantId)->get();
    }

    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Job::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Job
    {
        return Job::create($data);
    }

    public function update(Job $job, array $data): Job
    {
        $job->update($data);
        return $job->fresh();
    }

    public function delete(Job $job): bool
    {
        return $job->delete();
    }

    public function searchByTenant(int $tenantId, string $query): Collection
    {
        return Job::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->get();
    }

    public function getByStatus(int $tenantId, string $status): Collection
    {
        return Job::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get();
    }

    public function getByAssignedUser(int $tenantId, int $userId): Collection
    {
        return Job::where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->get();
    }
}
