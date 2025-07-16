<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobService extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'hours_worked',
        'notes',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getTotalPriceAttribute($value): float
    {
        if ($this->hours_worked && $this->service && $this->service->hourly_rate) {
            return $this->hours_worked * $this->service->hourly_rate;
        }

        return $this->quantity * $this->unit_price;
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    public function markAsCompleted(): void
    {
        $this->update(['completed_at' => now()]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->sort_order)) {
                $item->sort_order = static::where('job_id', $item->job_id)->max('sort_order') + 1;
            }
        });

        static::saving(function ($item) {
            $item->total_price = $item->getTotalPriceAttribute(null);
        });
    }
}
