<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerDetailService
{
    public function __construct(
        private CustomerDataEnrichmentService $enrichmentService
    ) {}

    /**
     * Get customer with optional enrichment
     */
    public function getCustomer(int $customerId, int $tenantId, Request $request): ?array
    {
        $customer = Customer::with([
            'jobs.jobServices.service',
            'appointments',
            'estimates.estimateItems',
            'assignedUser',
            'createdBy'
        ])->find($customerId);

        if (!$customer || $customer->tenant_id !== $tenantId) {
            return null;
        }

        $includeRelated = $request->boolean('include_related', true);

        if ($includeRelated) {
            return [
                'data' => $this->enrichmentService->enrichCustomerData($customer)
            ];
        }

        return [
            'data' => $customer
        ];
    }

    /**
     * Get customer summary
     */
    public function getCustomerSummary(int $customerId, int $tenantId): ?array
    {
        $customer = Customer::with([
            'jobs.jobServices.service',
            'appointments',
            'estimates.estimateItems',
            'assignedUser',
            'createdBy'
        ])->find($customerId);

        if (!$customer || $customer->tenant_id !== $tenantId) {
            return null;
        }

        $enrichedData = $this->enrichmentService->enrichCustomerData($customer);

        return [
            'data' => [
                'customer' => $customer,
                'summary' => $enrichedData['related_data']['summary'],
                'related_data' => $enrichedData['related_data']
            ]
        ];
    }
}
