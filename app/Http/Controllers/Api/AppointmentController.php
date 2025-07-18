<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AppointmentServiceInterface;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentServiceInterface $appointmentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        // Build filters from request parameters
        $filters = [];
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->get('date_from');
        }
        if ($request->has('date_to')) {
            $filters['date_to'] = $request->get('date_to');
        }
        if ($request->has('status')) {
            $filters['status'] = $request->get('status');
        }
        if ($request->has('assigned_to')) {
            $filters['assigned_to'] = $request->get('assigned_to');
        }

        $appointments = $this->appointmentService->getAppointments($user->tenant_id, $perPage, $filters);

        return response()->json([
            'data' => AppointmentResource::collection($appointments->items()),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
                'from' => $appointments->firstItem(),
                'to' => $appointments->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $appointment = $this->appointmentService->createAppointment(
                $request->all(),
                $user->tenant_id,
                $user->id
            );

            return response()->json([
                'message' => 'Appointment created successfully',
                'data' => new AppointmentResource($appointment)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $appointment = $this->appointmentService->updateAppointment($id, $request->all());
            return response()->json([
                'message' => 'Appointment updated successfully',
                'data' => new AppointmentResource($appointment)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $this->appointmentService->deleteAppointment($id);
        return response()->json([
            'message' => 'Appointment deleted successfully'
        ]);
    }

    /**
     * Get upcoming appointments.
     */
    public function upcoming(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);

        $appointments = $this->appointmentService->getUpcomingAppointments($user->tenant_id, $limit);

        return response()->json([
            'data' => AppointmentResource::collection($appointments)
        ]);
    }

    /**
     * Get appointments by date.
     */
    public function byDate(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', now()->format('Y-m-d'));

        $appointments = $this->appointmentService->getAppointmentsByDate($user->tenant_id, $date);

        return response()->json([
            'data' => AppointmentResource::collection($appointments)
        ]);
    }

    /**
     * Get appointments by user.
     */
    public function byUser(Request $request, $userId)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $appointments = $this->appointmentService->getAppointmentsByUser($user->tenant_id, $userId, $perPage);

        return response()->json([
            'data' => AppointmentResource::collection($appointments->items()),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
                'from' => $appointments->firstItem(),
                'to' => $appointments->lastItem(),
            ]
        ]);
    }

    /**
     * Get appointments by customer.
     */
    public function byCustomer(Request $request, $customerId)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $appointments = $this->appointmentService->getAppointmentsByCustomer($user->tenant_id, $customerId, $perPage);

        return response()->json([
            'data' => AppointmentResource::collection($appointments->items()),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
                'from' => $appointments->firstItem(),
                'to' => $appointments->lastItem(),
            ]
        ]);
    }

    /**
     * Check availability for a time slot.
     */
    public function checkAvailability(Request $request)
    {
        $user = Auth::user();
        $startTime = $request->get('start_time');
        $endTime = $request->get('end_time');
        $excludeAppointmentId = $request->get('exclude_appointment_id');
        $assignedTo = $request->get('assigned_to');

        if (!$startTime || !$endTime) {
            return response()->json([
                'message' => 'Start time and end time are required'
            ], 422);
        }

        $isAvailable = $this->appointmentService->checkAvailability(
            $user->tenant_id,
            $startTime,
            $endTime,
            $excludeAppointmentId,
            $assignedTo
        );

        return response()->json([
            'available' => $isAvailable
        ]);
    }

    /**
     * Mark appointment as confirmed.
     */
    public function markAsConfirmed($id)
    {
        try {
            $appointment = $this->appointmentService->markAsConfirmed($id);
            return response()->json([
                'message' => 'Appointment marked as confirmed',
                'data' => new AppointmentResource($appointment)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mark appointment as completed.
     */
    public function markAsCompleted($id)
    {
        try {
            $appointment = $this->appointmentService->markAsCompleted($id);
            return response()->json([
                'message' => 'Appointment marked as completed',
                'data' => new AppointmentResource($appointment)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mark appointment as cancelled.
     */
    public function markAsCancelled($id)
    {
        try {
            $appointment = $this->appointmentService->markAsCancelled($id);
            return response()->json([
                'message' => 'Appointment marked as cancelled',
                'data' => new AppointmentResource($appointment)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mark appointment as no show.
     */
    public function markAsNoShow($id)
    {
        try {
            $appointment = $this->appointmentService->markAsNoShow($id);
            return response()->json([
                'message' => 'Appointment marked as no show',
                'data' => new AppointmentResource($appointment)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
