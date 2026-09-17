<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', ['name' => 'Mara Santos', 'email' => 'mara@example.com', 'password' => 'password123', 'password_confirmation' => 'password123']);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'mara@example.com']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'registration']);
    }

    public function test_a_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['action' => 'login']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'logout']);
    }

    public function test_a_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/account', ['name' => 'Updated Name', 'email' => 'updated@example.com'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'email' => 'updated@example.com']);
    }
}