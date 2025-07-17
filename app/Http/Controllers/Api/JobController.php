<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Http\Resources\JobResource;

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
                'status' => 'required|in:scheduled,in_progress,completed,cancelled,on_hold',
                'priority' => 'required|in:low,medium,high,urgent',
                'scheduled_date' => 'nullable|date',
                'estimated_hours' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            $validated['tenant_id'] = auth()->user()->tenant_id;
            $validated['created_by'] = auth()->id();
            $validated['total_cost'] = $validated['price'] ?? 0; // Set total_cost to price or 0

            $job = Job::create($validated);
            $job->load('customer');

            return response()->json([
                'message' => 'Job created successfully',
                'job' => new JobResource($job)
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
        $job = Job::with('customer')->find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
        return new JobResource($job);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'customer_id' => 'required|exists:customers,id',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled,on_hold',
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

            $validated['total_cost'] = $validated['price'] ?? $job->total_cost; // Set total_cost to price or keep existing

            $job->update($validated);
            $job->load('customer');

            return response()->json([
                'message' => 'Job updated successfully',
                'job' => new JobResource($job)
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
        $job = Job::find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
        $job->delete();
        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }
}
