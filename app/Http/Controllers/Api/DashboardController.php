<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Estimate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            // Get statistics for the dashboard with error handling
            $totalCustomers = 0;
            $activeAppointments = 0;
            $completedThisMonth = 0;
            $revenueThisMonth = 0;
            $pendingEstimates = 0;
            $sentEstimates = 0;
            $upcomingAppointments = collect();
            $recentActivity = collect();

            // Query customers
            $totalCustomers = Customer::where('tenant_id', $user->tenant_id)->count();

            // Query appointments
            $activeAppointments = Appointment::where('tenant_id', $user->tenant_id)
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->count();
            $completedThisMonth = Appointment::where('tenant_id', $user->tenant_id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereYear('completed_at', Carbon::now()->year)
                ->count();

            // Calculate revenue for this month (using total_cost field from appointments)
            $revenueThisMonth = Appointment::where('tenant_id', $user->tenant_id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereYear('completed_at', Carbon::now()->year)
                ->sum('total_cost') ?? 0;

            // Query estimates
            $pendingEstimates = Estimate::where('tenant_id', $user->tenant_id)
                ->where('status', 'pending')
                ->count();
            $sentEstimates = Estimate::where('tenant_id', $user->tenant_id)
                ->where('status', 'sent')
                ->count();

            // Get upcoming appointments (scheduled for today or future)
            $upcomingAppointments = Appointment::where('tenant_id', $user->tenant_id)
                ->where('start_time', '>=', Carbon::now())
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'completed')
                ->with(['customer', 'assignedUser'])
                ->orderBy('start_time')
                ->limit(5)
                ->get();

            // Get recent estimates
            $recentEstimates = Estimate::where('tenant_id', $user->tenant_id)
                ->with(['customer', 'assignedUser'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'stats' => [
                    'totalCustomers' => $totalCustomers,
                    'activeAppointments' => $activeAppointments,
                    'completedThisMonth' => $completedThisMonth,
                    'revenueThisMonth' => $revenueThisMonth,
                    'pendingEstimates' => $pendingEstimates,
                    'sentEstimates' => $sentEstimates
                ],
                'upcomingAppointments' => $upcomingAppointments,
                'recentEstimates' => $recentEstimates,
                'recentActivity' => $recentActivity
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Dashboard data could not be loaded',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
