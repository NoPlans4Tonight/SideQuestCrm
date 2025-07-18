<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerDataEnrichmentService
{
    public function __construct(
        private JobService $jobService,
        private AppointmentService $appointmentService,
        private EstimateService $estimateService,
        private ServiceService $serviceService
    ) {}

    /**
     * Enrich customer data with related information
     */
    public function enrichCustomerData(Customer $customer): array
    {
        return [
            'customer' => $customer,
            'related_data' => [
                'jobs' => $this->getCustomerJobs($customer),
                'appointments' => $this->getCustomerAppointments($customer),
                'estimates' => $this->getCustomerEstimates($customer),
                'services' => $this->getCustomerServices($customer),
                'summary' => $this->getCustomerSummary($customer),
            ]
        ];
    }

        /**
     * Get customer jobs with status breakdown
     */
    private function getCustomerJobs(Customer $customer): array
    {
        $jobs = $customer->jobs()->with(['jobServices.service'])->get();

        if ($jobs->isEmpty()) {
            return [
                'has_jobs' => false,
                'total_count' => 0,
                'jobs' => [],
                'status_breakdown' => [],
                'total_value' => 0,
            ];
        }

        $statusBreakdown = $jobs->groupBy('status')->map->count();
        $totalValue = $jobs->sum('total_cost');

        return [
            'has_jobs' => true,
            'total_count' => $jobs->count(),
            'jobs' => $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'total_amount' => $job->total_cost,
                    'start_date' => $job->scheduled_date?->toISOString(),
                    'completion_date' => $job->completed_at?->toISOString(),
                    'services_count' => $job->jobServices->count(),
                ];
            }),
            'status_breakdown' => $statusBreakdown,
            'total_value' => $totalValue,
        ];
    }

    /**
     * Get customer appointments with status breakdown
     */
    private function getCustomerAppointments(Customer $customer): array
    {
        $appointments = $customer->appointments()->get();

        if ($appointments->isEmpty()) {
            return [
                'has_appointments' => false,
                'total_count' => 0,
                'appointments' => [],
                'status_breakdown' => [],
                'upcoming_count' => 0,
            ];
        }

        $statusBreakdown = $appointments->groupBy('status')->map->count();
        $upcomingCount = $appointments->where('start_time', '>', now())->count();

        return [
            'has_appointments' => true,
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
            'status_breakdown' => $statusBreakdown,
            'upcoming_count' => $upcomingCount,
        ];
    }

        /**
     * Get customer estimates with status breakdown
     */
    private function getCustomerEstimates(Customer $customer): array
    {
        $estimates = $customer->estimates()->with(['estimateItems'])->get();

        if ($estimates->isEmpty()) {
            return [
                'has_estimates' => false,
                'total_count' => 0,
                'estimates' => [],
                'status_breakdown' => [],
                'total_value' => 0,
                'pending_value' => 0,
            ];
        }

        $statusBreakdown = $estimates->groupBy('status')->map->count();
        $totalValue = $estimates->sum('total_amount');
        $pendingValue = $estimates->whereIn('status', ['draft', 'sent'])->sum('total_amount');

        return [
            'has_estimates' => true,
            'total_count' => $estimates->count(),
            'estimates' => $estimates->map(function ($estimate) {
                return [
                    'id' => $estimate->id,
                    'title' => $estimate->title,
                    'status' => $estimate->status,
                    'total_amount' => $estimate->total_amount,
                    'expiry_date' => $estimate->valid_until?->toISOString(),
                    'items_count' => $estimate->estimateItems->count(),
                    'is_expired' => $estimate->valid_until && $estimate->valid_until < now(),
                ];
            }),
            'status_breakdown' => $statusBreakdown,
            'total_value' => $totalValue,
            'pending_value' => $pendingValue,
        ];
    }

    /**
     * Get services associated with customer through jobs
     */
    private function getCustomerServices(Customer $customer): array
    {
        $jobServices = $customer->jobs()
            ->with(['jobServices.service'])
            ->get()
            ->flatMap(function ($job) {
                return $job->jobServices;
            });

        if ($jobServices->isEmpty()) {
            return [
                'has_services' => false,
                'total_count' => 0,
                'services' => [],
                'unique_services' => [],
            ];
        }

        $uniqueServices = $jobServices->map(function ($jobService) {
            return $jobService->service;
        })->unique('id');

        return [
            'has_services' => true,
            'total_count' => $jobServices->count(),
            'services' => $jobServices->map(function ($jobService) {
                return [
                    'id' => $jobService->id,
                    'service_name' => $jobService->service->name,
                    'quantity' => $jobService->quantity,
                    'unit_price' => $jobService->unit_price,
                    'total_price' => $jobService->total_price,
                    'job_id' => $jobService->job_id,
                ];
            }),
            'unique_services' => $uniqueServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                ];
            }),
        ];
    }

    /**
     * Get customer summary statistics
     */
    private function getCustomerSummary(Customer $customer): array
    {
        $jobs = $customer->jobs;
        $appointments = $customer->appointments;
        $estimates = $customer->estimates;

        return [
            'total_jobs' => $jobs->count(),
            'active_jobs' => $jobs->whereIn('status', ['scheduled', 'in_progress'])->count(),
            'completed_jobs' => $jobs->where('status', 'completed')->count(),
            'total_appointments' => $appointments->count(),
            'upcoming_appointments' => $appointments->where('start_time', '>', now())->count(),
            'total_estimates' => $estimates->count(),
            'pending_estimates' => $estimates->whereIn('status', ['draft', 'sent'])->count(),
            'accepted_estimates' => $estimates->where('status', 'accepted')->count(),
            'total_job_value' => $jobs->sum('total_cost'),
            'total_estimate_value' => $estimates->sum('total_amount'),
            'pending_estimate_value' => $estimates->whereIn('status', ['draft', 'sent'])->sum('total_amount'),
            'last_activity' => $this->getLastActivity($customer),
            'customer_since' => $customer->created_at?->toISOString(),
        ];
    }

    /**
     * Get the last activity date for the customer
     */
    private function getLastActivity(Customer $customer): ?string
    {
        $dates = collect([
            $customer->updated_at,
            $customer->jobs->max('updated_at'),
            $customer->appointments->max('updated_at'),
            $customer->estimates->max('updated_at'),
        ])->filter();

        return $dates->max()?->toISOString();
    }

    /**
     * Enrich multiple customers with related data
     */
    public function enrichCustomersData(Collection $customers): array
    {
        return $customers->map(function ($customer) {
            return $this->enrichCustomerData($customer);
        })->toArray();
    }
}
