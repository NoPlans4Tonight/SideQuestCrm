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
            'estimated_hours' => $this->estimated_hours,
            'price' => $this->price,
            'total_cost' => $this->total_cost,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
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
