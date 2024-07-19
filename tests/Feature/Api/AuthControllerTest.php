<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        // create a user
        $response = $this->postJson('/api/v1/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cpf' => '63598812094',
            'phone' => '11987654321'
        ]);

        // checks if the user was created successfully
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function user_can_login()
    {
        // create a user
        $user = User::factory()->create(['password' => bcrypt('password')]);

        // login
        $response = $this->postJson('/api/v1/auth/signin', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        // checks whether the login was successful
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token']]);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        // create a user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // log out
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/signout');

        // checks whether the logout was successful
        $response->assertStatus(200);
    }
}
