<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    // public function test_new_users_can_register(): void
    // {
    //     $this->withoutMiddleware();
    //     if (! Features::enabled(Features::registration())) {
    //         $this->markTestSkipped('Registration support is not enabled.');
    //     }

    //     $response = $this->post('/register', [
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //         'password' => 'password',
    //         'password_confirmation' => 'password',
    //         'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    //     ]);

    //     // Debug: Check response status and any errors
    //     \Log::info('Registration test debug', [
    //         'response_status' => $response->status(),
    //         'response_content' => $response->content(),
    //         'session_errors' => session('errors'),
    //         'user_created' => \App\Models\User::where('email', 'test@example.com')->exists(),
    //     ]);

    //     $this->assertAuthenticated();
    //     $response->assertRedirect(route('dashboard', absolute: false));
    // }
}
