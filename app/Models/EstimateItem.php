<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getTotalPriceAttribute($value): float
    {
        return $this->quantity * $this->unit_price;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->sort_order)) {
                $item->sort_order = static::where('estimate_id', $item->estimate_id)->max('sort_order') + 1;
            }
        });

        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
