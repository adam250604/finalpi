<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '1234567890',                 // Added
            'address' => '123 Main St, Anytown',     // Added
            'role' => 'patient',                     // Added
            'date_of_birth' => '1990-01-01',         // Added
            'gender' => 'male',                      // Added
            'emergency_contact_name' => 'Jane Doe',  // Added
            'emergency_contact_phone' => '0987654321',// Added
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
