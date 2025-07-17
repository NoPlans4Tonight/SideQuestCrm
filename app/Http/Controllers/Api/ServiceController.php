<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Services\ServiceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Service;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceServiceInterface $serviceService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $services = $this->serviceService->getServices($user->tenant_id, 15);

        return response()->json([
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'from' => $services->firstItem(),
                'to' => $services->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $service = $this->serviceService->createService(
                $request->all(),
                $user->tenant_id,
                $user->id
            );

            return response()->json([
                'message' => 'Service created successfully',
                'data' => $service
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show($id)
    {
        $service = $this->serviceService->getService($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json(['data' => $service]);
    }

    public function update(Request $request, $id)
    {
        try {
            $updatedService = $this->serviceService->updateService($id, $request->all());
            return response()->json([
                'message' => 'Service updated successfully',
                'data' => $updatedService
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->serviceService->deleteService($id);
            return response()->json([
                'message' => 'Service deleted successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
