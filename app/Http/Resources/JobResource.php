<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'scheduled_date' => $this->scheduled_date,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'duration' => $this->duration,
            'has_datetime' => $this->hasDateTime(),
            'is_all_day' => $this->isAllDay(),
            'estimated_hours' => $this->estimated_hours,
            'price' => $this->price,
            'total_cost' => $this->total_cost,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ];
            }),
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'first_name' => $this->customer->first_name,
                    'last_name' => $this->customer->last_name,
                ];
            }),
        ];
    }
}
