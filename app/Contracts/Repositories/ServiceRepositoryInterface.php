<?php

namespace App\Contracts\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ServiceRepositoryInterface
{
    public function findById(int $id): ?Service;
    public function findByTenant(int $tenantId): Collection;
    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Service;
    public function update(Service $service, array $data): Service;
    public function delete(Service $service): bool;
    public function searchByTenant(int $tenantId, string $query): Collection;
    public function getByCategory(int $tenantId, string $category): Collection;
    public function getActiveServices(int $tenantId): Collection;
    public function getByCreatedBy(int $tenantId, int $userId): Collection;
}
