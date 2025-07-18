<?php

namespace App\Services;

use App\Contracts\Services\AppointmentServiceInterface;
use App\Contracts\Repositories\AppointmentRepositoryInterface;
use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AppointmentService implements AppointmentServiceInterface
{
    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepository
    ) {}

    public function getAppointments(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->appointmentRepository->getAppointments($tenantId, $perPage);
    }

    public function getAppointmentById(int $id): ?Appointment
    {
        return $this->appointmentRepository->getAppointmentById($id);
    }

    public function createAppointment(array $data, int $tenantId, int $createdBy): Appointment
    {
        $this->validateAppointmentData($data);

        $data['tenant_id'] = $tenantId;
        $data['created_by'] = $createdBy;

        // Check availability if start_time and end_time are provided
        if (isset($data['start_time']) && isset($data['end_time'])) {
            if (!$this->checkAvailability($tenantId, $data['start_time'], $data['end_time'])) {
                throw ValidationException::withMessages([
                    'time_slot' => 'The selected time slot conflicts with an existing appointment.'
                ]);
            }
        }

        return $this->appointmentRepository->createAppointment($data);
    }

    public function updateAppointment(int $id, array $data): Appointment
    {
        $this->validateAppointmentData($data, $id);

        $appointment = $this->appointmentRepository->getAppointmentById($id);
        if (!$appointment) {
            throw ValidationException::withMessages([
                'appointment' => 'Appointment not found.'
            ]);
        }

        // Check availability if time is being changed
        if (isset($data['start_time']) && isset($data['end_time'])) {
            if (!$this->checkAvailability($appointment->tenant_id, $data['start_time'], $data['end_time'], $id)) {
                throw ValidationException::withMessages([
                    'time_slot' => 'The selected time slot conflicts with an existing appointment.'
                ]);
            }
        }

        return $this->appointmentRepository->updateAppointment($id, $data);
    }

    public function deleteAppointment(int $id): bool
    {
        return $this->appointmentRepository->deleteAppointment($id);
    }

    public function getUpcomingAppointments(int $tenantId, int $limit = 10): Collection
    {
        return $this->appointmentRepository->getUpcomingAppointments($tenantId, $limit);
    }

    public function getAppointmentsByDate(int $tenantId, string $date): Collection
    {
        return $this->appointmentRepository->getAppointmentsByDate($tenantId, $date);
    }

    public function getAppointmentsByUser(int $tenantId, int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->appointmentRepository->getAppointmentsByUser($tenantId, $userId, $perPage);
    }

    public function getAppointmentsByCustomer(int $tenantId, int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->appointmentRepository->getAppointmentsByCustomer($tenantId, $customerId, $perPage);
    }

    public function checkAvailability(int $tenantId, string $startTime, string $endTime, ?int $excludeAppointmentId = null): bool
    {
        return $this->appointmentRepository->checkAvailability($tenantId, $startTime, $endTime, $excludeAppointmentId);
    }

    public function markAsConfirmed(int $id): Appointment
    {
        $appointment = $this->appointmentRepository->getAppointmentById($id);
        if (!$appointment) {
            throw ValidationException::withMessages([
                'appointment' => 'Appointment not found.'
            ]);
        }

        $appointment->markAsConfirmed();
        return $appointment->fresh(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy']);
    }

    public function markAsCompleted(int $id): Appointment
    {
        $appointment = $this->appointmentRepository->getAppointmentById($id);
        if (!$appointment) {
            throw ValidationException::withMessages([
                'appointment' => 'Appointment not found.'
            ]);
        }

        $appointment->markAsCompleted();
        return $appointment->fresh(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy']);
    }

    public function markAsCancelled(int $id): Appointment
    {
        $appointment = $this->appointmentRepository->getAppointmentById($id);
        if (!$appointment) {
            throw ValidationException::withMessages([
                'appointment' => 'Appointment not found.'
            ]);
        }

        $appointment->markAsCancelled();
        return $appointment->fresh(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy']);
    }

    public function markAsNoShow(int $id): Appointment
    {
        $appointment = $this->appointmentRepository->getAppointmentById($id);
        if (!$appointment) {
            throw ValidationException::withMessages([
                'appointment' => 'Appointment not found.'
            ]);
        }

        $appointment->markAsNoShow();
        return $appointment->fresh(['customer', 'lead', 'estimate', 'assignedUser', 'createdBy']);
    }

    private function validateAppointmentData(array $data, ?int $appointmentId = null): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'estimate_id' => 'nullable|exists:estimates,id',
            'appointment_type' => 'required|in:estimate,inspection,repair,maintenance,follow_up,other',
            'start_time' => 'required|date|after:now',
            'end_time' => 'nullable|date|after:start_time',
            'duration' => 'nullable|integer|min:15|max:480', // 15 minutes to 8 hours
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // If updating, make start_time optional
        if ($appointmentId) {
            $rules['start_time'] = 'nullable|date';
        }

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}
