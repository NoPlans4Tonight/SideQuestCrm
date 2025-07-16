<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Job extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'estimate_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'scheduled_date',
        'started_at',
        'completed_at',
        'total_hours',
        'materials_cost',
        'labor_cost',
        'total_cost',
        'notes',
        'location',
        'job_type',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'status',
                'priority',
                'assigned_to',
                'scheduled_date',
                'started_at',
                'completed_at',
                'total_cost',
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

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobServices(): HasMany
    {
        return $this->hasMany(JobService::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getTotalCostAttribute($value): float
    {
        return $this->materials_cost + $this->labor_cost;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
