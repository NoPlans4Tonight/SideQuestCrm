<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'company_name',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'timezone',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }



    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
