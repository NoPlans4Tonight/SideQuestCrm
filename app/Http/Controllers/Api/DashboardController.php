<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get statistics for the dashboard
        $totalCustomers = Customer::count();
        $activeJobs = Job::where('status', '!=', 'completed')->count();
        $completedThisMonth = Job::where('status', 'completed')
            ->whereMonth('completed_at', Carbon::now()->month)
            ->whereYear('completed_at', Carbon::now()->year)
            ->count();

        // Calculate revenue for this month (assuming jobs have a price field)
        $revenueThisMonth = Job::where('status', 'completed')
            ->whereMonth('completed_at', Carbon::now()->month)
            ->whereYear('completed_at', Carbon::now()->year)
            ->sum('price') ?? 0;

        // Get upcoming jobs (scheduled for today or future)
        $upcomingJobs = Job::where('scheduled_date', '>=', Carbon::today())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->with('customer')
            ->orderBy('scheduled_date')
            ->limit(5)
            ->get();

        // Get recent activity (you can expand this based on your needs)
        $recentActivity = collect(); // Placeholder for now

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
    }
}
