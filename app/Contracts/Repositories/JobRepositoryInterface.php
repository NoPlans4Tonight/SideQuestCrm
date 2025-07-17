<?php

namespace App\Contracts\Repositories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JobRepositoryInterface
{
    public function findById(int $id): ?Job;
    public function findByTenant(int $tenantId): Collection;
    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Job;
    public function update(Job $job, array $data): Job;
    public function delete(Job $job): bool;
    public function searchByTenant(int $tenantId, string $query): Collection;
    public function getByStatus(int $tenantId, string $status): Collection;
    public function getByAssignedUser(int $tenantId, int $userId): Collection;
}
