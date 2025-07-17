<?php

namespace App\Services;

use App\Contracts\Repositories\ServiceRepositoryInterface;
use App\Contracts\Services\ServiceServiceInterface;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ServiceService implements ServiceServiceInterface
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function getServices(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->serviceRepository->paginateByTenant($tenantId, $perPage);
    }

    public function getService(int $id): ?Service
    {
        return $this->serviceRepository->findById($id);
    }

    public function createService(array $data, int $tenantId, int $userId): Service
    {
        $validatedData = $this->validateServiceData($data);

        $serviceData = array_merge($validatedData, [
            'tenant_id' => $tenantId,
            'created_by' => $userId,
        ]);

        return $this->serviceRepository->create($serviceData);
    }

    public function updateService(int $id, array $data): Service
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            throw new \InvalidArgumentException('Service not found');
        }

        $validatedData = $this->validateServiceData($data, $id);

        return $this->serviceRepository->update($service, $validatedData);
    }

    public function deleteService(int $id): bool
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            throw new \InvalidArgumentException('Service not found');
        }

        return $this->serviceRepository->delete($service);
    }

    public function searchServices(int $tenantId, string $query): Collection
    {
        return $this->serviceRepository->searchByTenant($tenantId, $query);
    }

    public function getServicesByCategory(int $tenantId, string $category): Collection
    {
        return $this->serviceRepository->getByCategory($tenantId, $category);
    }

    public function getActiveServices(int $tenantId): Collection
    {
        return $this->serviceRepository->getActiveServices($tenantId);
    }

    public function getServicesByCreatedBy(int $tenantId, int $userId): Collection
    {
        return $this->serviceRepository->getByCreatedBy($tenantId, $userId);
    }

    public function validateServiceData(array $data, ?int $serviceId = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ];

        // Add unique name validation if service ID is provided (for updates)
        if ($serviceId) {
            $rules['name'] = 'required|string|max:255|unique:services,name,' . $serviceId;
        } else {
            $rules['name'] = 'required|string|max:255|unique:services,name';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
