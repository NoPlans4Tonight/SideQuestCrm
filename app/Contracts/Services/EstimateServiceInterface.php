<?php

namespace App\Contracts\Services;

use App\Models\Estimate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EstimateServiceInterface
{
    public function getEstimates(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function getEstimate(int $id, int $tenantId): ?Estimate;
    public function createEstimate(array $data, int $tenantId, int $userId): Estimate;
    public function updateEstimate(int $id, array $data, int $tenantId): Estimate;
    public function deleteEstimate(int $id, int $tenantId): bool;
    public function searchEstimates(int $tenantId, string $query): Collection;
    public function getEstimatesByStatus(int $tenantId, string $status): Collection;
    public function getEstimatesByCustomer(int $tenantId, int $customerId): Collection;
    public function getEstimatesByAssignedUser(int $tenantId, int $userId): Collection;
    public function markEstimateAsSent(int $id, int $tenantId): Estimate;
    public function markEstimateAsAccepted(int $id, int $tenantId): Estimate;
    public function markEstimateAsRejected(int $id, int $tenantId): Estimate;
    public function markEstimateAsExpired(int $id, int $tenantId): Estimate;
    public function getExpiredEstimates(int $tenantId): Collection;
    public function getPendingEstimates(int $tenantId): Collection;
    public function getSentEstimates(int $tenantId): Collection;
    public function getAcceptedEstimates(int $tenantId): Collection;
    public function getRejectedEstimates(int $tenantId): Collection;
    public function generatePdf(int $id, int $tenantId): string;
    public function calculateTotals(Estimate $estimate): array;
}
