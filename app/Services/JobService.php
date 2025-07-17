<?php

namespace App\Services;

use App\Contracts\Repositories\JobRepositoryInterface;
use App\Contracts\Services\JobServiceInterface;
use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JobService implements JobServiceInterface
{
    public function __construct(
        private JobRepositoryInterface $jobRepository
    ) {}

    public function getJobs(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->jobRepository->paginateByTenant($tenantId, $perPage);
    }

    public function getJob(int $id): ?Job
    {
        return $this->jobRepository->findById($id);
    }

    public function createJob(array $data, int $tenantId, int $userId): Job
    {
        $validatedData = $this->validateJobData($data);
        $jobData = array_merge($validatedData, [
            'tenant_id' => $tenantId,
            'created_by' => $userId,
        ]);
        return $this->jobRepository->create($jobData);
    }

    public function updateJob(int $id, array $data): Job
    {
        $job = $this->jobRepository->findById($id);
        if (!$job) {
            throw new \InvalidArgumentException('Job not found');
        }
        $validatedData = $this->validateJobData($data, $id);
        return $this->jobRepository->update($job, $validatedData);
    }

    public function deleteJob(int $id): bool
    {
        $job = $this->jobRepository->findById($id);
        if (!$job) {
            throw new \InvalidArgumentException('Job not found');
        }
        return $this->jobRepository->delete($job);
    }

    public function searchJobs(int $tenantId, string $query): Collection
    {
        return $this->jobRepository->searchByTenant($tenantId, $query);
    }

    public function getJobsByStatus(int $tenantId, string $status): Collection
    {
        return $this->jobRepository->getByStatus($tenantId, $status);
    }

    public function getJobsByAssignedUser(int $tenantId, int $userId): Collection
    {
        return $this->jobRepository->getByAssignedUser($tenantId, $userId);
    }

    public function assignJobToUser(int $jobId, int $userId): Job
    {
        $job = $this->jobRepository->findById($jobId);
        if (!$job) {
            throw new \InvalidArgumentException('Job not found');
        }
        return $this->jobRepository->update($job, ['assigned_to' => $userId]);
    }

    public function unassignJob(int $jobId): Job
    {
        $job = $this->jobRepository->findById($jobId);
        if (!$job) {
            throw new \InvalidArgumentException('Job not found');
        }
        return $this->jobRepository->update($job, ['assigned_to' => null]);
    }

    public function validateJobData(array $data, ?int $jobId = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,on_hold',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }
}
