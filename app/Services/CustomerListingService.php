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
        $search = $request->get('search');

        // Use optimized query based on requirements
        if ($includeRelated) {
            $customers = $this->customerService->getCustomersWithSummary($tenantId, $perPage);
        } else {
            $customers = $this->customerService->getCustomers($tenantId, $perPage);
        }

        // Apply search if provided
        if ($search) {
            $customers = $this->applySearch($customers, $search);
        }

        // Apply filters if needed
        if ($filter !== 'all') {
            $customers = $this->applyFilter($customers, $filter);
        }

        // Only enrich if explicitly requested and not already loaded
        if ($includeRelated && !$customers->getCollection()->first()?->relationLoaded('appointments')) {
            $enrichedData = $this->enrichmentService->enrichCustomersData($customers->getCollection());
            return [
                'data' => $enrichedData,
                'meta' => $this->getPaginationMeta($customers)
            ];
        }

        // Return optimized data with basic summary
        if ($includeRelated) {
            return [
                'data' => $this->getOptimizedCustomerData($customers->getCollection()),
                'meta' => $this->getPaginationMeta($customers)
            ];
        }

        return [
            'data' => \App\Http\Resources\CustomerResource::collection($customers->items()),
            'meta' => $this->getPaginationMeta($customers)
        ];
    }

    /**
     * Get optimized customer data with pre-loaded relationships
     */
    private function getOptimizedCustomerData(Collection $customers): array
    {
        return $customers->map(function ($customer) {
            $appointments = $customer->appointments ?? collect();
            $estimates = $customer->estimates ?? collect();

            return [
                'customer' => [
                    'id' => $customer->id,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                    'assigned_user' => $customer->assignedUser ? [
                        'id' => $customer->assignedUser->id,
                        'name' => $customer->assignedUser->name,
                        'email' => $customer->assignedUser->email,
                    ] : null,
                    'created_by' => $customer->createdBy ? [
                        'id' => $customer->createdBy->id,
                        'name' => $customer->createdBy->name,
                        'email' => $customer->createdBy->email,
                    ] : null,
                    'created_at' => $customer->created_at?->toISOString(),
                    'updated_at' => $customer->updated_at?->toISOString(),
                ],
                'related_data' => [
                    'appointments' => [
                        'has_appointments' => $appointments->isNotEmpty(),
                        'total_count' => $appointments->count(),
                        'appointments' => $appointments->map(function ($appointment) {
                            return [
                                'id' => $appointment->id,
                                'title' => $appointment->title,
                                'status' => $appointment->status,
                                'start_time' => $appointment->start_time?->toISOString(),
                                'end_time' => $appointment->end_time?->toISOString(),
                                'is_upcoming' => $appointment->start_time > now(),
                            ];
                        }),
                        'status_breakdown' => $appointments->groupBy('status')->map->count(),
                        'upcoming_count' => $appointments->where('start_time', '>', now())->count(),
                    ],
                    'estimates' => [
                        'has_estimates' => $estimates->isNotEmpty(),
                        'total_count' => $estimates->count(),
                        'estimates' => $estimates->map(function ($estimate) {
                            return [
                                'id' => $estimate->id,
                                'title' => $estimate->title,
                                'status' => $estimate->status,
                                'total_amount' => $estimate->total_amount,
                                'expiry_date' => $estimate->valid_until?->toISOString(),
                                'is_expired' => $estimate->valid_until && $estimate->valid_until < now(),
                            ];
                        }),
                        'status_breakdown' => $estimates->groupBy('status')->map->count(),
                        'total_value' => $estimates->sum('total_amount'),
                        'pending_value' => $estimates->whereIn('status', ['draft', 'sent'])->sum('total_amount'),
                    ],
                    'services' => [
                        'has_services' => $appointments->whereNotNull('service_id')->isNotEmpty(),
                        'total_count' => $appointments->whereNotNull('service_id')->count(),
                        'services' => $appointments->whereNotNull('service_id')->map(function ($appointment) {
                            return [
                                'id' => $appointment->id,
                                'service_name' => $appointment->service->name ?? 'No Service',
                                'quantity' => 1,
                                'unit_price' => $appointment->service->base_price ?? 0,
                                'total_price' => $appointment->service->base_price ?? 0,
                                'appointment_id' => $appointment->id,
                            ];
                        }),
                        'unique_services' => $appointments->whereNotNull('service_id')->map(function ($appointment) {
                            return $appointment->service;
                        })->unique('id')->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                                'description' => $service->description,
                                'price' => $service->base_price,
                            ];
                        }),
                    ],
                    'summary' => [
                        'total_appointments' => $appointments->count(),
                        'upcoming_appointments' => $appointments->where('start_time', '>', now())->count(),
                        'completed_appointments' => $appointments->where('status', 'completed')->count(),
                        'total_estimates' => $estimates->count(),
                        'pending_estimates' => $estimates->whereIn('status', ['draft', 'sent'])->count(),
                        'accepted_estimates' => $estimates->where('status', 'accepted')->count(),
                        'total_appointment_value' => $appointments->sum('total_cost'),
                        'total_estimate_value' => $estimates->sum('total_amount'),
                        'pending_estimate_value' => $estimates->whereIn('status', ['draft', 'sent'])->sum('total_amount'),
                        'last_activity' => $this->getLastActivity($customer),
                        'customer_since' => $customer->created_at?->toISOString(),
                    ]
                ]
            ];
        })->toArray();
    }

    /**
     * Get the last activity date for the customer
     */
    private function getLastActivity($customer): ?string
    {
        $appointments = $customer->appointments ?? collect();
        $estimates = $customer->estimates ?? collect();

        $dates = collect([
            $customer->updated_at,
            $appointments->max('updated_at'),
            $estimates->max('updated_at'),
        ])->filter();

        return $dates->max()?->toISOString();
    }

    /**
     * Apply filters to customers
     */
    private function applyFilter(LengthAwarePaginator $customers, string $filter): LengthAwarePaginator
    {
        $collection = $customers->getCollection();

        switch ($filter) {
            case 'active_appointments':
                $filtered = $collection->filter(fn($customer) =>
                    $customer->appointments->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])->isNotEmpty()
                );
                break;

            case 'pending_estimates':
                $filtered = $collection->filter(fn($customer) =>
                    $customer->estimates->whereIn('status', ['draft', 'sent', 'pending'])->isNotEmpty()
                );
                break;

            case 'has_services':
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
     * Apply search to customers
     */
    private function applySearch(LengthAwarePaginator $customers, string $search): LengthAwarePaginator
    {
        $collection = $customers->getCollection();

        $filtered = $collection->filter(function ($customer) use ($search) {
            $searchLower = strtolower($search);
            return str_contains(strtolower($customer->first_name), $searchLower) ||
                   str_contains(strtolower($customer->last_name), $searchLower) ||
                   str_contains(strtolower($customer->email ?? ''), $searchLower) ||
                   str_contains(strtolower($customer->phone ?? ''), $searchLower);
        });

        return new LengthAwarePaginator(
            $filtered->values(),
            $customers->total(),
            $customers->perPage(),
            $customers->currentPage(),
            ['path' => request()->url()]
        );
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
