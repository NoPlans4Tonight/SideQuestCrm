<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FollowUp extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'lead_id',
        'estimate_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'assigned_to',
        'created_by',
        'notes',
        'reminder_sent',
        'reminder_sent_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'type',
                'status',
                'priority',
                'due_date',
                'completed_at',
                'assigned_to',
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now() && $this->status !== 'completed';
    }

    public function isDueToday(): bool
    {
        return $this->due_date && $this->due_date->isToday() && $this->status !== 'completed';
    }

    public function isDueSoon(): bool
    {
        return $this->due_date &&
               $this->due_date->between(now(), now()->addDays(3)) &&
               $this->status !== 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function sendReminder(): void
    {
        // This would integrate with your email/SMS service
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'completed');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
                    ->where('status', '!=', 'completed');
    }

    public function scopeDueSoon($query)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays(3)])
                    ->where('status', '!=', 'completed');
    }
}
