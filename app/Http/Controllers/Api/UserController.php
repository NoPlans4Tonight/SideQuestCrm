<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Appointment;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $users = User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'position')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Get user's schedule/availability for appointment scheduling
     */
    public function getSchedule($userId, Request $request)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $date = $request->get('date', now()->format('Y-m-d'));
            $startDate = $request->get('start_date', $date);
            $endDate = $request->get('end_date', $date);

            // Get appointments for the user with proper date filtering
            $appointments = Appointment::with('customer')
                ->where('assigned_to', $userId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('status', '!=', 'cancelled')
                ->orderBy('start_time')
                ->get();

            // Format the schedule data
            $schedule = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'appointments' => $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'title' => $appointment->title,
                        'start_time' => $appointment->start_time,
                        'end_time' => $appointment->end_time,
                        'duration' => $appointment->duration,
                        'type' => 'appointment',
                        'status' => $appointment->status,
                        'customer' => $appointment->customer ? [
                            'id' => $appointment->customer->id,
                            'name' => $appointment->customer->full_name
                        ] : null
                    ];
                }),
            ];

            return response()->json($schedule);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching schedule'], 500);
        }
    }
}
