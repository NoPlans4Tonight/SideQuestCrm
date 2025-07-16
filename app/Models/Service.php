<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'category',
        'base_price',
        'hourly_rate',
        'is_active',
        'created_by',
        'settings',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'category',
                'base_price',
                'hourly_rate',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estimateItems(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function jobServices(): HasMany
    {
        return $this->hasMany(JobService::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getDisplayPriceAttribute(): string
    {
        if ($this->hourly_rate > 0) {
            return '$' . number_format($this->hourly_rate, 2) . '/hr';
        }

        return '$' . number_format($this->base_price, 2);
    }

    public function calculatePrice($hours = null): float
    {
        if ($hours && $this->hourly_rate > 0) {
            return $this->hourly_rate * $hours;
        }

        return $this->base_price ?? 0;
    }
}
