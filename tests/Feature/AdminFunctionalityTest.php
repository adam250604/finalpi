<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_index_page(): void
    {
        // Create a user with admin role
        $user = User::factory()->create(['role' => 'admin']);

        // Act as the authenticated admin
        $response = $this->actingAs($user)->get(route('users.index'));

        // Assert the page is accessible
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }
}
