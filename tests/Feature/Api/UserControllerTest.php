<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function can_list_users()
    {
        // create an already verified admin user
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $token = $admin->createToken('auth_token')->plainTextToken;

        // create users
        User::factory()->count(3)->create();

        // get the list of users
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');

        // checks whether the user listing was successful.
        $response->assertStatus(200)
            ->assertJsonCount(13, 'data');
    }

    /** @test */
    public function can_get_single_user()
    {
        // create an admin user
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $token = $admin->createToken('auth_token')->plainTextToken;

        // create a user
        $fetchedUser = User::factory()->create();

        // get a specific user by ID
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/users/{$fetchedUser->id}");

        // checks whether the user query for the specific ID was successful
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $fetchedUser->id]]);
    }
}
