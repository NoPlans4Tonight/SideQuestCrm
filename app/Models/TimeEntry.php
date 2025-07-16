<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'start_time',
        'end_time',
        'duration',
        'description',
        'notes',
        'is_billable',
        'hourly_rate',
        'total_amount',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'is_billable' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute($value): int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        return $value ?? 0;
    }

    public function getDurationHoursAttribute(): float
    {
        return $this->duration / 60;
    }

    public function getTotalAmountAttribute($value): float
    {
        if ($this->is_billable && $this->hourly_rate) {
            return ($this->duration / 60) * $this->hourly_rate;
        }

        return $value ?? 0;
    }

    public function isRunning(): bool
    {
        return !is_null($this->start_time) && is_null($this->end_time);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->start_time) && !is_null($this->end_time);
    }

    public function start(): void
    {
        $this->update(['start_time' => now()]);
    }

    public function stop(): void
    {
        $this->update(['end_time' => now()]);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            if ($entry->start_time && $entry->end_time) {
                $entry->duration = $entry->start_time->diffInMinutes($entry->end_time);
            }
        });
    }
}
