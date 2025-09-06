<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_access_their_patient_list(): void
    {
        // Create a user and associated doctor profile
        $user = User::factory()->create(['role' => 'doctor']);
        Doctor::factory()->create(['user_id' => $user->id]);

        // Act as the authenticated doctor
        $response = $this->actingAs($user)->get(route('doctor.patients'));

        // Assert the page is accessible
        $response->assertStatus(200);
        $response->assertViewIs('doctors.patients');
    }
}
