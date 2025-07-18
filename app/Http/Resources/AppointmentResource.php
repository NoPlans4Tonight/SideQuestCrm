<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'appointment_type' => $this->appointment_type,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'duration' => $this->duration,
            'status' => $this->status,
            'location' => $this->location,
            'notes' => $this->notes,
            'reminder_sent' => $this->reminder_sent,
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'first_name' => $this->customer->first_name,
                    'last_name' => $this->customer->last_name,
                    'full_name' => $this->customer->full_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),

            'lead' => $this->whenLoaded('lead', function () {
                return [
                    'id' => $this->lead->id,
                    'first_name' => $this->lead->first_name,
                    'last_name' => $this->lead->last_name,
                    'full_name' => $this->lead->full_name,
                    'email' => $this->lead->email,
                    'phone' => $this->lead->phone,
                ];
            }),

            'estimate' => $this->whenLoaded('estimate', function () {
                return [
                    'id' => $this->estimate->id,
                    'estimate_number' => $this->estimate->estimate_number,
                    'title' => $this->estimate->title,
                    'status' => $this->estimate->status,
                    'total_amount' => $this->estimate->total_amount,
                ];
            }),

            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ];
            }),

            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),

            // Computed properties
            'is_scheduled' => $this->isScheduled(),
            'is_confirmed' => $this->isConfirmed(),
            'is_completed' => $this->isCompleted(),
            'is_cancelled' => $this->isCancelled(),
            'is_no_show' => $this->isNoShow(),
            'is_upcoming' => $this->isUpcoming(),
            'is_past' => $this->isPast(),

            // Formatted dates for display
            'formatted_start_time' => $this->start_time?->format('M j, Y g:i A'),
            'formatted_end_time' => $this->end_time?->format('M j, Y g:i A'),
            'formatted_duration' => $this->duration ? $this->formatDuration($this->duration) : null,
        ];
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '');
        }

        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . $remainingMinutes . ' min';
    }
}
