<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\CustomVerifyEmail;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_payment()
    {
        // Falsificar notificações para capturar o e-mail de verificação
        Notification::fake();

        // Criação do usuário via requisição
        $userResponse = $this->postJson('/api/v1/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cpf' => '63598812094',
            'phone' => '11987654321'
        ]);

        $userResponse->assertStatus(201);

        // Falsificar o envio do e-mail e obter o link de verificação
        Notification::assertSentTo(User::first(), CustomVerifyEmail::class, function ($notification, $channels) use (&$verificationUrl) {
            $mailMessage = $notification->toMail(User::first());
            $verificationUrl = $mailMessage->actionUrl;

            // Ajustar a URL para o formato correto
            $parsedUrl = parse_url($verificationUrl);
            parse_str($parsedUrl['query'], $queryParams);
            $verificationUrl = url("/api/v1/auth/email/verify/{$queryParams['id']}?expires={$queryParams['expires']}&signature={$queryParams['signature']}");
            return true;
        });

        // Simular a ativação do usuário via requisição
        $activationResponse = $this->getJson($verificationUrl);
        $activationResponse->assertStatus(200);

        // Autenticação do usuário e obtenção do token
        $loginResponse = $this->postJson('/api/v1/auth/signin', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse['data']['token'];

        // Criação de um campo via requisição
        $fieldResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/fields', [
                'name' => 'Test Field',
                'location' => 'Test Location',
                'type' => 'Football',
                'hourly_rate' => 50
            ]);

        $fieldResponse->assertStatus(201);
        $fieldId = $fieldResponse['data']['id'];

        // Criação de uma reserva via requisição
        $reservationResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/reservations', [
                'field_id' => $fieldId,
                'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
                'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'status' => 'WAITING'
            ]);

        $reservationResponse->assertStatus(201);
        $reservationId = $reservationResponse['data']['id'];

        // Criação do pagamento via requisição
        $paymentResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post("/api/v1/payments/reservations/{$reservationId}/pay");

        // Verificação da resposta e da presença do pagamento no banco de dados
        $paymentResponse->assertStatus(200);
        $this->assertDatabaseHas('payments', ['reservation_id' => $reservationId]);
    }
}
