<?php

namespace Tests;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected User $user;
    protected Tenant $tenant;

    protected function authenticateUser(): User
    {
        // Create a tenant and user for testing if they don't exist
        if (!isset($this->tenant)) {
            $this->tenant = Tenant::factory()->create();
        }

        if (!isset($this->user)) {
            $this->user = User::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        }

        $this->actingAs($this->user);
        return $this->user;
    }
}
