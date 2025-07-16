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

            // Check if tables exist before querying
            if (Schema::hasTable('customers')) {
                $totalCustomers = Customer::count();
            }

            if (Schema::hasTable('crm_jobs')) {
                $activeJobs = Job::where('status', '!=', 'completed')->count();
                $completedThisMonth = Job::where('status', 'completed')
                    ->whereMonth('completed_at', Carbon::now()->month)
                    ->whereYear('completed_at', Carbon::now()->year)
                    ->count();

                // Calculate revenue for this month (using total_cost field)
                $revenueThisMonth = Job::where('status', 'completed')
                    ->whereMonth('completed_at', Carbon::now()->month)
                    ->whereYear('completed_at', Carbon::now()->year)
                    ->sum('total_cost') ?? 0;

                // Get upcoming jobs (scheduled for today or future)
                $upcomingJobs = Job::where('scheduled_date', '>=', Carbon::today())
                    ->where('status', '!=', 'completed')
                    ->where('status', '!=', 'cancelled')
                    ->with('customer')
                    ->orderBy('scheduled_date')
                    ->limit(5)
                    ->get();
            }

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
            return response()->json([
                'error' => 'Dashboard data could not be loaded',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
