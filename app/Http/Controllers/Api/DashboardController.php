<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
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
            $activeJobs = 0;
            $completedThisMonth = 0;
            $revenueThisMonth = 0;
            $upcomingJobs = collect();
            $recentActivity = collect();

            // Query customers
            $totalCustomers = Customer::where('tenant_id', $user->tenant_id)->count();

            // Query jobs
            $activeJobs = Job::where('tenant_id', $user->tenant_id)
                ->where('status', '!=', 'completed')
                ->count();
            $completedThisMonth = Job::where('tenant_id', $user->tenant_id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereYear('completed_at', Carbon::now()->year)
                ->count();

            // Calculate revenue for this month (using total_cost field)
            $revenueThisMonth = Job::where('tenant_id', $user->tenant_id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereYear('completed_at', Carbon::now()->year)
                ->sum('total_cost') ?? 0;

            // Get upcoming jobs (scheduled for today or future)
            $upcomingJobs = Job::where('tenant_id', $user->tenant_id)
                ->where('scheduled_date', '>=', Carbon::today())
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->with('customer')
                ->orderBy('scheduled_date')
                ->limit(5)
                ->get();

            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'stats' => [
                    'totalCustomers' => $totalCustomers,
                    'activeJobs' => $activeJobs,
                    'completedThisMonth' => $completedThisMonth,
                    'revenueThisMonth' => $revenueThisMonth
                ],
                'upcomingJobs' => $upcomingJobs,
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
