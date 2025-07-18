<?php

namespace App\Contracts\Repositories;

use App\Models\Estimate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EstimateRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Estimate;
    public function findByTenant(int $tenantId): Collection;
    public function paginateByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Estimate;
    public function update(Estimate $estimate, array $data): Estimate;
    public function delete(Estimate $estimate): bool;
    public function findByEstimateNumber(string $estimateNumber, int $tenantId): ?Estimate;
    public function searchByTenant(int $tenantId, string $query): Collection;
    public function getByStatus(int $tenantId, string $status): Collection;
    public function getByCustomer(int $tenantId, int $customerId): Collection;
    public function getByAssignedUser(int $tenantId, int $userId): Collection;
    public function getExpiredEstimates(int $tenantId): Collection;
    public function getPendingEstimates(int $tenantId): Collection;
    public function getSentEstimates(int $tenantId): Collection;
    public function getAcceptedEstimates(int $tenantId): Collection;
    public function getRejectedEstimates(int $tenantId): Collection;
}
