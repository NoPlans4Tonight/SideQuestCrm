<?php

namespace App\Observers;

use App\Models\Estimate;
use Illuminate\Support\Facades\Log;

class EstimateObserver
{
    /**
     * Handle the Estimate "created" event.
     */
    public function created(Estimate $estimate): void
    {
        Log::info('Estimate created', [
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
            'customer_id' => $estimate->customer_id,
            'created_by' => $estimate->created_by,
        ]);
    }

    /**
     * Handle the Estimate "updated" event.
     */
    public function updated(Estimate $estimate): void
    {
        Log::info('Estimate updated', [
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
            'status' => $estimate->status,
            'total_amount' => $estimate->total_amount,
        ]);
    }

    /**
     * Handle the Estimate "deleted" event.
     */
    public function deleted(Estimate $estimate): void
    {
        Log::info('Estimate deleted', [
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
            'customer_id' => $estimate->customer_id,
        ]);
    }

    /**
     * Handle the Estimate "restored" event.
     */
    public function restored(Estimate $estimate): void
    {
        Log::info('Estimate restored', [
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
        ]);
    }

    /**
     * Handle the Estimate "force deleted" event.
     */
    public function forceDeleted(Estimate $estimate): void
    {
        Log::info('Estimate force deleted', [
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
        ]);
    }
}
