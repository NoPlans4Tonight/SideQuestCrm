<?php

namespace App\Contracts\Services;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AppointmentServiceInterface
{
    public function getAppointments(int $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function getAppointmentById(int $id): ?Appointment;
    public function createAppointment(array $data, int $tenantId, int $createdBy): Appointment;
    public function updateAppointment(int $id, array $data): Appointment;
    public function deleteAppointment(int $id): bool;
    public function getUpcomingAppointments(int $tenantId, int $limit = 10): Collection;
    public function getAppointmentsByDate(int $tenantId, string $date): Collection;
    public function getAppointmentsByUser(int $tenantId, int $userId, int $perPage = 15): LengthAwarePaginator;
    public function getAppointmentsByCustomer(int $tenantId, int $customerId, int $perPage = 15): LengthAwarePaginator;
    public function checkAvailability(int $tenantId, string $startTime, string $endTime, ?int $excludeAppointmentId = null, ?int $assignedTo = null): bool;
    public function markAsConfirmed(int $id): Appointment;
    public function markAsCompleted(int $id): Appointment;
    public function markAsCancelled(int $id): Appointment;
    public function markAsNoShow(int $id): Appointment;
}
