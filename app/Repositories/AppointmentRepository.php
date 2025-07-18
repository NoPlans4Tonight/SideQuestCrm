<?php

namespace App\Repositories;

use App\Contracts\Repositories\AppointmentRepositoryInterface;
use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function getAppointments(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::where('tenant_id', $tenantId)
            ->with(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy'])
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);
    }

    public function getAppointmentById(int $id): ?Appointment
    {
        return Appointment::with(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->find($id);
    }

    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function updateAppointment(int $id, array $data): Appointment
    {
        $appointment = Appointment::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $appointment->update($data);
        return $appointment->fresh(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy']);
    }

    public function deleteAppointment(int $id): bool
    {
        $appointment = Appointment::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        return $appointment->delete();
    }

    public function getUpcomingAppointments(int $tenantId, int $limit = 10): Collection
    {
        return Appointment::where('tenant_id', $tenantId)
            ->where('start_time', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->with(['customer', 'assignedUser'])
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function getAppointmentsByDate(int $tenantId, string $date): Collection
    {
        return Appointment::where('tenant_id', $tenantId)
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->with(['customer', 'assignedUser'])
            ->orderBy('start_time')
            ->get();
    }

    public function getAppointmentsByUser(int $tenantId, int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->with(['customer', 'lead', 'estimate'])
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);
    }

    public function getAppointmentsByCustomer(int $tenantId, int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->with(['assignedUser', 'lead', 'estimate'])
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);
    }

    public function checkAvailability(int $tenantId, string $startTime, string $endTime, ?int $excludeAppointmentId = null): bool
    {
        $query = Appointment::where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->count() === 0;
    }
}
