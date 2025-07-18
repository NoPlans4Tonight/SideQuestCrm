<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'lead_id',
        'estimate_id',
        'service_id',
        'title',
        'description',
        'appointment_type',
        'start_time',
        'end_time',
        'duration',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'location',
        'notes',
        'reminder_sent',
        'reminder_sent_at',
        'materials_cost',
        'labor_cost',
        'total_cost',
        'price',
        'estimated_hours',
        'total_hours',
        'started_at',
        'completed_at',
        'scheduled_date',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'materials_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'price' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'appointment_type',
                'start_time',
                'end_time',
                'status',
                'assigned_to',
                'location',
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isNoShow(): bool
    {
        return $this->status === 'no_show';
    }

    public function isUpcoming(): bool
    {
        return $this->start_time > now() && $this->status === 'scheduled';
    }

    public function isPast(): bool
    {
        return $this->start_time < now();
    }

    public function markAsConfirmed(): void
    {
        $this->update(['status' => 'confirmed']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markAsNoShow(): void
    {
        $this->update(['status' => 'no_show']);
    }

    public function sendReminder(): void
    {
        // This would integrate with your email/SMS service
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);
    }

    // Job-specific methods
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsOnHold(): void
    {
        $this->update(['status' => 'on_hold']);
    }

    public function hasDateTime(): bool
    {
        return !is_null($this->start_time) && !is_null($this->end_time);
    }

    public function isAllDay(): bool
    {
        return !is_null($this->scheduled_date) && is_null($this->start_time) && is_null($this->end_time);
    }

    public function getDurationHoursAttribute(): float
    {
        return $this->duration / 60;
    }



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->end_time) && $appointment->start_time && $appointment->duration) {
                $appointment->end_time = $appointment->start_time->addMinutes($appointment->duration);
            }
        });
    }
}
