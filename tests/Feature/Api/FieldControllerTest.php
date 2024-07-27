<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FieldControllerTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function can_list_fields()
    {
        // create fields
        Field::factory()->count(3)->create();

        // get the list of fields
        $response = $this->getJson('/api/v1/fields');

        // checks whether the field listing was successful
        $response->assertStatus(200)
            ->assertJsonCount(13, 'data');
    }

    /** @test */
    public function can_get_single_field()
    {
        // create fields
        $field = Field::factory()->create();

        // get a specific field
        $response = $this->getJson("/api/v1/fields/{$field->id}");

        // checks if the field was found by ID
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $field->id]]);
    }

    /** @test */
    public function admin_can_create_field()
    {
        // create an administrator user
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $token = $admin->createToken('auth_token')->plainTextToken;

        // create a field
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/fields', [
                'name' => 'Test Field',
                'location' => 'Test Location',
                'type' => 'Football',
                'hourly_rate' => 50,
                'images' => [UploadedFile::fake()->image('field.jpg', 1024, 768)]
            ]);

        // checks whether field creation was successful
        $response->assertStatus(201);
        $this->assertDatabaseHas('fields', ['name' => 'Test Field']);
    }
}
