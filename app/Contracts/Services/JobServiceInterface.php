<?php

namespace App\Contracts\Services;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JobServiceInterface
{
    public function getJobs(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function getJob(int $id): ?Job;
    public function createJob(array $data, int $tenantId, int $userId): Job;
    public function updateJob(int $id, array $data): Job;
    public function deleteJob(int $id): bool;
    public function searchJobs(int $tenantId, string $query): Collection;
    public function getJobsByStatus(int $tenantId, string $status): Collection;
    public function getJobsByAssignedUser(int $tenantId, int $userId): Collection;
    public function assignJobToUser(int $jobId, int $userId): Job;
    public function unassignJob(int $jobId): Job;
    public function validateJobData(array $data, ?int $jobId = null): array;
}
