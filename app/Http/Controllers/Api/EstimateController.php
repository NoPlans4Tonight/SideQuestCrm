<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Services\EstimateServiceInterface;
use App\Http\Resources\EstimateResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EstimateController extends Controller
{
    public function __construct(
        private EstimateServiceInterface $estimateService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $estimates = $this->estimateService->getEstimates($user->tenant_id, $perPage);

        return response()->json([
            'data' => EstimateResource::collection($estimates),
            'pagination' => [
                'current_page' => $estimates->currentPage(),
                'last_page' => $estimates->lastPage(),
                'per_page' => $estimates->perPage(),
                'total' => $estimates->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->createEstimate($request->all(), $user->tenant_id, $user->id);

            return response()->json([
                'message' => 'Estimate created successfully',
                'data' => new EstimateResource($estimate)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Laravel handle validation exceptions
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create estimate',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $estimate = $this->estimateService->getEstimate($id, $user->tenant_id);

        if (!$estimate) {
            return response()->json(['message' => 'Estimate not found'], 404);
        }

        return response()->json([
            'data' => new EstimateResource($estimate)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->updateEstimate($id, $request->all(), $user->tenant_id);

            return response()->json([
                'message' => 'Estimate updated successfully',
                'data' => new EstimateResource($estimate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update estimate',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $this->estimateService->deleteEstimate($id, $user->tenant_id);

            return response()->json([
                'message' => 'Estimate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete estimate',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Search estimates.
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->get('query', '');

        if (empty($query)) {
            return response()->json(['message' => 'Search query is required'], 400);
        }

        $estimates = $this->estimateService->searchEstimates($user->tenant_id, $query);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get estimates by status.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getEstimatesByStatus($user->tenant_id, $status);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get estimates by customer.
     */
    public function byCustomer(int $customerId): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getEstimatesByCustomer($user->tenant_id, $customerId);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Mark estimate as sent.
     */
    public function markAsSent(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->markEstimateAsSent($id, $user->tenant_id);

            return response()->json([
                'message' => 'Estimate marked as sent successfully',
                'data' => new EstimateResource($estimate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark estimate as sent',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mark estimate as accepted.
     */
    public function markAsAccepted(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->markEstimateAsAccepted($id, $user->tenant_id);

            return response()->json([
                'message' => 'Estimate marked as accepted successfully',
                'data' => new EstimateResource($estimate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark estimate as accepted',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mark estimate as rejected.
     */
    public function markAsRejected(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->markEstimateAsRejected($id, $user->tenant_id);

            return response()->json([
                'message' => 'Estimate marked as rejected successfully',
                'data' => new EstimateResource($estimate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark estimate as rejected',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mark estimate as expired.
     */
    public function markAsExpired(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $estimate = $this->estimateService->markEstimateAsExpired($id, $user->tenant_id);

            return response()->json([
                'message' => 'Estimate marked as expired successfully',
                'data' => new EstimateResource($estimate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark estimate as expired',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate PDF for estimate.
     */
    public function generatePdf(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $pdfContent = $this->estimateService->generatePdf($id, $user->tenant_id);

            return response()->json([
                'message' => 'PDF generated successfully',
                'pdf_content' => $pdfContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get expired estimates.
     */
    public function expired(): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getExpiredEstimates($user->tenant_id);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get pending estimates.
     */
    public function pending(): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getPendingEstimates($user->tenant_id);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get sent estimates.
     */
    public function sent(): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getSentEstimates($user->tenant_id);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get accepted estimates.
     */
    public function accepted(): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getAcceptedEstimates($user->tenant_id);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Get rejected estimates.
     */
    public function rejected(): JsonResponse
    {
        $user = Auth::user();
        $estimates = $this->estimateService->getRejectedEstimates($user->tenant_id);

        return response()->json([
            'data' => EstimateResource::collection($estimates)
        ]);
    }
}
