<?php

namespace App\Contracts\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ServiceServiceInterface
{
    public function getServices(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function getService(int $id): ?Service;
    public function createService(array $data, int $tenantId, int $userId): Service;
    public function updateService(int $id, array $data): Service;
    public function deleteService(int $id): bool;
    public function searchServices(int $tenantId, string $query): Collection;
    public function getServicesByCategory(int $tenantId, string $category): Collection;
    public function getActiveServices(int $tenantId): Collection;
    public function getServicesByCreatedBy(int $tenantId, int $userId): Collection;
    public function validateServiceData(array $data, ?int $serviceId = null): array;
}
