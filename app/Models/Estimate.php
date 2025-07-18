<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Estimate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'lead_id',
        'estimate_number',
        'title',
        'description',
        'status',
        'valid_until',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'created_by',
        'assigned_to',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'expired_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'estimate_number',
                'title',
                'status',
                'total_amount',
                'assigned_to',
                'sent_at',
                'accepted_at',
                'rejected_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estimateItems(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }



    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function getTotalAmountAttribute($value): float
    {
        $subtotal = $this->subtotal ?? 0;
        $taxAmount = $this->tax_amount ?? 0;
        $discountAmount = $this->discount_amount ?? 0;

        return $subtotal + $taxAmount - $discountAmount;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsAccepted(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function markAsRejected(): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($estimate) {
            if (empty($estimate->estimate_number)) {
                $estimate->estimate_number = static::generateEstimateNumber();
            }
        });
    }

    protected static function generateEstimateNumber(): string
    {
        $prefix = 'EST';
        $year = date('Y');
        $lastEstimate = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEstimate ? (int) substr($lastEstimate->estimate_number, -4) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }
}
