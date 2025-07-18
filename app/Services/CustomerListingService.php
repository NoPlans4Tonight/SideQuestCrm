<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerListingService
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerDataEnrichmentService $enrichmentService
    ) {}

    /**
     * Get customers with optional enrichment and filtering
     */
    public function getCustomers(Request $request, int $tenantId): array
    {
        $perPage = $request->get('per_page', 15);
        $includeRelated = $request->boolean('include_related', true);
        $filter = $request->get('filter', 'all');

        // Get base customers
        $customers = $this->customerService->getCustomers($tenantId, $perPage);

        // Apply filters if needed
        if ($filter !== 'all') {
            $customers = $this->applyFilter($customers, $filter);
        }

        // Always enrich data for frontend
        if ($includeRelated) {
            $this->loadRelationships($customers->getCollection());
            $enrichedData = $this->enrichmentService->enrichCustomersData($customers->getCollection());

            return [
                'data' => $enrichedData,
                'meta' => $this->getPaginationMeta($customers)
            ];
        }

        return [
            'data' => \App\Http\Resources\CustomerResource::collection($customers->items()),
            'meta' => $this->getPaginationMeta($customers)
        ];
    }

    /**
     * Apply filters to customers
     */
    private function applyFilter(LengthAwarePaginator $customers, string $filter): LengthAwarePaginator
    {
        $collection = $customers->getCollection();

        switch ($filter) {
            case 'active_appointments':
                $collection->load(['appointments' => function ($query) {
                    $query->whereIn('status', ['scheduled', 'confirmed', 'in_progress']);
                }]);
                $filtered = $collection->filter(fn($customer) => $customer->appointments->isNotEmpty());
                break;

            case 'pending_estimates':
                $collection->load(['estimates' => function ($query) {
                    $query->whereIn('status', ['draft', 'sent', 'pending']);
                }]);
                $filtered = $collection->filter(fn($customer) => $customer->estimates->isNotEmpty());
                break;

            case 'has_services':
                $collection->load(['appointments.service']);
                $filtered = $collection->filter(function ($customer) {
                    return $customer->appointments->whereNotNull('service_id')->isNotEmpty();
                });
                break;

            default:
                return $customers;
        }

        // Create new paginator with filtered results
        return new LengthAwarePaginator(
            $filtered->values(),
            $customers->total(),
            $customers->perPage(),
            $customers->currentPage(),
            ['path' => request()->url()]
        );
    }

    /**
     * Load relationships for enrichment
     */
    private function loadRelationships(Collection $customers): void
    {
        $customers->load([
            'appointments.service',
            'estimates.estimateItems',
            'assignedUser',
            'createdBy'
        ]);
    }

    /**
     * Get pagination metadata
     */
    private function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
