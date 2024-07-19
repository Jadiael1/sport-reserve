<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_payment()
    {
        // create an already verified admin user
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
            'cpf' => '60985194049'
        ]);
        $token = $admin->createToken('auth_token')->plainTextToken;

        // create a field
        $field = Field::factory()->create();

        // create a reservation
        $reservation = Reservation::factory()->create([
            'user_id' => $admin->id,
            'status' => 'WAITING',
            'field_id' => $field->id
        ]);

        // create a payment
        $paymentResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post("/api/v1/payments/reservations/{$reservation->id}/pay");

        // checks if the payment was created successfully
        $paymentResponse->assertStatus(200);
        $this->assertDatabaseHas('payments', ['reservation_id' => $reservation->id]);
    }
}
