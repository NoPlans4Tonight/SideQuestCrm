<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        Log::info('Appointment created', [
            'appointment_id' => $appointment->id,
            'title' => $appointment->title,
            'customer_id' => $appointment->customer_id,
            'start_time' => $appointment->start_time,
            'created_by' => $appointment->created_by,
        ]);
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        Log::info('Appointment updated', [
            'appointment_id' => $appointment->id,
            'title' => $appointment->title,
            'status' => $appointment->status,
            'changes' => $appointment->getChanges(),
        ]);
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        Log::info('Appointment deleted', [
            'appointment_id' => $appointment->id,
            'title' => $appointment->title,
            'customer_id' => $appointment->customer_id,
        ]);
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        Log::info('Appointment restored', [
            'appointment_id' => $appointment->id,
            'title' => $appointment->title,
        ]);
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        Log::info('Appointment force deleted', [
            'appointment_id' => $appointment->id,
            'title' => $appointment->title,
        ]);
    }
}
