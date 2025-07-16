<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = Job::with('customer')->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($jobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'customer_id' => 'required|exists:customers,id',
                'status' => 'required|in:pending,in_progress,completed,cancelled',
                'priority' => 'required|in:low,medium,high,urgent',
                'scheduled_date' => 'nullable|date',
                'estimated_hours' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            $job = Job::create($validated);

            return response()->json([
                'message' => 'Job created successfully',
                'job' => $job->load('customer')
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
    public function show(Job $job)
    {
        $job->load('customer');
        return response()->json($job);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'customer_id' => 'required|exists:customers,id',
                'status' => 'required|in:pending,in_progress,completed,cancelled',
                'priority' => 'required|in:low,medium,high,urgent',
                'scheduled_date' => 'nullable|date',
                'estimated_hours' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Set completed_at when status changes to completed
            if ($validated['status'] === 'completed' && $job->status !== 'completed') {
                $validated['completed_at'] = Carbon::now();
            }

            $job->update($validated);

            return response()->json([
                'message' => 'Job updated successfully',
                'job' => $job->load('customer')
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
    public function destroy(Job $job)
    {
        $job->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }
}
