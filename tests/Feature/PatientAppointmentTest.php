<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_access_appointment_create_page(): void
    {
        // Create a user and associated patient profile
        $user = User::factory()->create(['role' => 'patient']);
        Patient::factory()->create(['user_id' => $user->id]);

        // Act as the authenticated patient
        $response = $this->actingAs($user)->get(route('appointments.create'));

        // Assert the page is accessible
        $response->assertStatus(200);
        $response->assertViewIs('appointments.create');
    }
}
