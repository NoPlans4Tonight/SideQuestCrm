<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerListingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerListingOrchestratorService
{
    public function __construct(
        private CustomerServiceInterface $customerService,
        private CustomerListingService $listingService
    ) {}

    /**
     * Get customers based on request parameters and user context
     */
    public function getCustomersForRequest(Request $request, int $tenantId): array
    {
        if ($this->shouldReturnSimpleList($request)) {
            return $this->getSimpleCustomerList($tenantId);
        }

        return $this->getEnrichedCustomerList($request, $tenantId);
    }

    /**
     * Determine if we should return a simple customer list
     */
    private function shouldReturnSimpleList(Request $request): bool
    {
        return $request->boolean('simple', false);
    }

    /**
     * Get simple customer list for dropdowns and forms
     */
    private function getSimpleCustomerList(int $tenantId): array
    {
        $customers = $this->customerService->getCustomers($tenantId, 1000);

        return [
            'data' => CustomerResource::collection($customers->items()),
            'meta' => $this->buildPaginationMeta($customers)
        ];
    }

    /**
     * Get enriched customer list with caching
     */
    private function getEnrichedCustomerList(Request $request, int $tenantId): array
    {
        if ($this->shouldUseCaching($request)) {
            return $this->getCachedCustomerList($request, $tenantId);
        }

        return $this->listingService->getCustomers($request, $tenantId);
    }

    /**
     * Determine if we should use caching for this request
     */
    private function shouldUseCaching(Request $request): bool
    {
        return !$request->has('filter') &&
               !$request->has('search') &&
               $request->get('per_page', 15) == 15;
    }

    /**
     * Get cached customer list
     */
    private function getCachedCustomerList(Request $request, int $tenantId): array
    {
        $cacheKey = $this->buildCacheKey($request, $tenantId);
        $cacheDuration = 300; // 5 minutes

        return cache()->remember($cacheKey, $cacheDuration, function () use ($request, $tenantId) {
            return $this->listingService->getCustomers($request, $tenantId);
        });
    }

    /**
     * Build cache key for customer listing
     */
    private function buildCacheKey(Request $request, int $tenantId): string
    {
        return "customers_{$tenantId}_" . md5($request->fullUrl());
    }

    /**
     * Build pagination metadata
     */
    private function buildPaginationMeta(LengthAwarePaginator $paginator): array
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
