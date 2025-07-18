<?php

namespace App\Services;

use App\Contracts\Repositories\EstimateRepositoryInterface;
use App\Contracts\Services\EstimateServiceInterface;
use App\Models\Estimate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EstimateService implements EstimateServiceInterface
{
    public function __construct(
        private EstimateRepositoryInterface $estimateRepository
    ) {}

    public function getEstimates(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->estimateRepository->paginateByTenant($tenantId, $perPage);
    }

    public function getEstimate(int $id, int $tenantId): ?Estimate
    {
        return $this->estimateRepository->findById($id, $tenantId);
    }

    public function createEstimate(array $data, int $tenantId, int $userId): Estimate
    {
        $validatedData = $this->validateEstimateData($data);

        $estimateData = array_merge($validatedData, [
            'tenant_id' => $tenantId,
            'created_by' => $userId,
        ]);

        $estimate = $this->estimateRepository->create($estimateData);

        // Create estimate items if provided
        if (isset($data['estimate_items']) && is_array($data['estimate_items'])) {
            $this->createEstimateItems($estimate, $data['estimate_items']);
        }

        // Calculate and update totals
        $totals = $this->calculateTotals($estimate);
        $this->estimateRepository->update($estimate, $totals);

        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function updateEstimate(int $id, array $data, int $tenantId): Estimate
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        $validatedData = $this->validateEstimateData($data, $id);

        $estimate = $this->estimateRepository->update($estimate, $validatedData);

        // Recalculate totals
        $totals = $this->calculateTotals($estimate);
        $this->estimateRepository->update($estimate, $totals);

        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function deleteEstimate(int $id, int $tenantId): bool
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        return $this->estimateRepository->delete($estimate);
    }

    public function searchEstimates(int $tenantId, string $query): Collection
    {
        return $this->estimateRepository->searchByTenant($tenantId, $query);
    }

    public function getEstimatesByStatus(int $tenantId, string $status): Collection
    {
        return $this->estimateRepository->getByStatus($tenantId, $status);
    }

    public function getEstimatesByCustomer(int $tenantId, int $customerId): Collection
    {
        return $this->estimateRepository->getByCustomer($tenantId, $customerId);
    }

    public function getEstimatesByAssignedUser(int $tenantId, int $userId): Collection
    {
        return $this->estimateRepository->getByAssignedUser($tenantId, $userId);
    }

    public function markEstimateAsSent(int $id, int $tenantId): Estimate
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        $estimate->markAsSent();
        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function markEstimateAsAccepted(int $id, int $tenantId): Estimate
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        $estimate->markAsAccepted();
        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function markEstimateAsRejected(int $id, int $tenantId): Estimate
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        $estimate->markAsRejected();
        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function markEstimateAsExpired(int $id, int $tenantId): Estimate
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        $estimate->markAsExpired();
        return $estimate->fresh(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);
    }

    public function getExpiredEstimates(int $tenantId): Collection
    {
        return $this->estimateRepository->getExpiredEstimates($tenantId);
    }

    public function getPendingEstimates(int $tenantId): Collection
    {
        return $this->estimateRepository->getPendingEstimates($tenantId);
    }

    public function getSentEstimates(int $tenantId): Collection
    {
        return $this->estimateRepository->getSentEstimates($tenantId);
    }

    public function getAcceptedEstimates(int $tenantId): Collection
    {
        return $this->estimateRepository->getAcceptedEstimates($tenantId);
    }

    public function getRejectedEstimates(int $tenantId): Collection
    {
        return $this->estimateRepository->getRejectedEstimates($tenantId);
    }

    public function generatePdf(int $id, int $tenantId): string
    {
        $estimate = $this->estimateRepository->findById($id, $tenantId);

        if (!$estimate) {
            throw new \InvalidArgumentException('Estimate not found');
        }

        // Load relationships for PDF generation
        $estimate->load(['customer', 'lead', 'assignedUser', 'createdBy', 'estimateItems.service']);

        // This would integrate with a PDF library like DomPDF or Snappy
        // For now, return a placeholder
        return "PDF content for estimate {$estimate->estimate_number}";
    }

    public function calculateTotals(Estimate $estimate): array
    {
        $subtotal = $estimate->estimateItems->sum('total_price');
        $taxAmount = $subtotal * ($estimate->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount - $estimate->discount_amount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    private function validateEstimateData(array $data, ?int $estimateId = null): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,sent,accepted,rejected,expired',
            'valid_until' => 'nullable|date|after:today',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'estimate_items' => 'nullable|array',
            'estimate_items.*.service_id' => 'nullable|exists:services,id',
            'estimate_items.*.description' => 'required|string|max:255',
            'estimate_items.*.quantity' => 'required|numeric|min:0.01',
            'estimate_items.*.unit_price' => 'required|numeric|min:0',
            'estimate_items.*.total_price' => 'required|numeric|min:0',
            'estimate_items.*.notes' => 'nullable|string',
            'estimate_items.*.sort_order' => 'nullable|integer|min:0',
        ];

        if ($estimateId) {
            // For updates, make some fields optional
            $rules['customer_id'] = 'sometimes|required|exists:customers,id';
            $rules['title'] = 'sometimes|required|string|max:255';
            $rules['status'] = 'sometimes|required|in:draft,pending,sent,accepted,rejected,expired';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function createEstimateItems(Estimate $estimate, array $items): void
    {
        foreach ($items as $item) {
            $estimate->estimateItems()->create([
                'tenant_id' => $estimate->tenant_id,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'notes' => $item['notes'] ?? null,
                'sort_order' => $item['sort_order'] ?? 0,
            ]);
        }
    }
}
