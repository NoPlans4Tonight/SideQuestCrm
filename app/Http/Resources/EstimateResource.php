<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estimate_number' => $this->estimate_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'valid_until' => $this->valid_until,
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'sent_at' => $this->sent_at,
            'accepted_at' => $this->accepted_at,
            'rejected_at' => $this->rejected_at,
            'expired_at' => $this->expired_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'first_name' => $this->customer->first_name,
                    'last_name' => $this->customer->last_name,
                    'full_name' => $this->customer->full_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'address' => $this->customer->address,
                    'city' => $this->customer->city,
                    'state' => $this->customer->state,
                    'zip_code' => $this->customer->zip_code,
                    'country' => $this->customer->country,
                    'full_address' => $this->customer->full_address,
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
                    'status' => $this->lead->status,
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
            'estimate_items' => $this->whenLoaded('estimateItems', function () {
                return $this->estimateItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'service_id' => $item->service_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'notes' => $item->notes,
                        'sort_order' => $item->sort_order,
                        'service' => $item->service ? [
                            'id' => $item->service->id,
                            'name' => $item->service->name,
                            'description' => $item->service->description,
                            'category' => $item->service->category,
                            'base_price' => $item->service->base_price,
                            'hourly_rate' => $item->service->hourly_rate,
                        ] : null,
                    ];
                });
            }),
            'estimate_items_count' => $this->whenCounted('estimateItems'),
            'jobs_count' => $this->whenCounted('jobs'),
            'follow_ups_count' => $this->whenCounted('followUps'),
            'is_pending' => $this->isPending(),
            'is_sent' => $this->isSent(),
            'is_accepted' => $this->isAccepted(),
            'is_rejected' => $this->isRejected(),
            'is_expired' => $this->isExpired(),
        ];
    }
}
