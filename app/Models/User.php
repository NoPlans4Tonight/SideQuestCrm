<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'position',
        'is_active',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_to');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function assignedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'assigned_to');
    }

    public function assignedEstimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'assigned_to');
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'assigned_to');
    }

    public function assignedFollowUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_to');
    }

    public function createdCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function createdJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'created_by');
    }

    public function createdEstimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }
}
