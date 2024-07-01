<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Reservation;
use App\Models\Payments;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/payments/reservations/{id}/pay",
     *     operationId="initiatePayment",
     *     tags={"Payments"},
     *     summary="Initiate payment for a reservation",
     *     description="Creates a payment request for a reservation and returns the payment URL",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the reservation"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment link generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment link generated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url", type="string", example="https://pagamento.sandbox.pagbank.com.br/pagamento?code=example-code")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to initiate payment",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to initiate payment"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error_messages", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="error", type="string", example="invalid_request_body"),
     *                         @OA\Property(property="description", type="string", example="There are some syntax errors in the request payload. Please check the documentation.")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function initiatePayment(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = $reservation->user;
        $field = Field::findOrFail($reservation->field_id);

        $startTime = Carbon::parse($reservation->start_time);
        $endTime = Carbon::parse($reservation->end_time);

        $durationInHours = $startTime->diffInHours($endTime);
        $totalAmount = $durationInHours * $reservation->field->hourly_rate * 100;

        $body = array(
            'customer' => array(
                'email' => $user->email,
                'tax_id' => $user->cpf
            ),
            'reference_id' => "{$reservation->field_id}-{$reservation->id}-{$user->id}",
            "customer_modifiable" => true,
            'items' => array(
                array(
                    'reference_id' => "{$reservation->field_id}-{$reservation->id}-{$user->id}",
                    'name' => "Reserva {$field->name}",
                    'description' => "Reserva de uma quadra esportiva",
                    'quantity' => 1,
                    'unit_amount' => $totalAmount,
                ),
            ),
            'payment_methods' => array(
                array('type' => "PIX"),
                array('type' => "debit_card"),
                array('type' => "credit_card"),
            ),
            "payment_methods_configs" => array(
                array(
                    "type" => "credit_card",
                    "config_options" => array(
                        array(
                            "option" => "installments_limit",
                            "value" => "1"
                        )
                    )
                )
            ),
            'redirect_url' => env('SAP_URL'),
            'return_url' => env('SAP_URL'),
            'soft_descriptor' => 'sport-reserve',
            'payment_notification_urls' => array(env('APP_URL') . "/api/v1/payments/notify")
        );
        $url = config('pagseguro.environment') === 'sandbox' ? config('pagseguro.baseUrlSandBox') . "/checkouts" : config('pagseguro.baseUrl') . "/checkouts";
        $token = config('pagseguro.environment') === 'sandbox' ? config('pagseguro.tokenSandBox') : config('pagseguro.token');
        $response = Http::withHeaders([
            'Authorization' => "Bearer " . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url, $body);

        if ($response->successful()) {
            $responseData = $response->json();
            $payLink = collect($responseData['links'])->firstWhere('rel', 'PAY')['href'] ?? null;
            return response()->json([
                'status' => 'success',
                'message' => 'Payment link generated successfully.',
                'data' => array('url' => $payLink),
                'errors' => null
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment',
                'data' => null,
                'errors' => $response->json()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/notify",
     *     operationId="paymentNotification",
     *     tags={"Payments"},
     *     summary="Handle payment notification",
     *     description="Handles the payment notification from PagSeguro and updates the reservation status",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="notificationCode", type="string", example="EXAMPLE-NOTIFICATION-CODE")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment notification processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Payment notification processed.")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error processing payment notification",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Error message")
     *             )
     *         )
     *     )
     * )
     */
    public function paymentNotification(Request $request)
    {
        $notificationCode = $request->input('notificationCode');
        $notificationType = $request->input('notificationType');
        $method = '';
        if ($request->isMethod('post')) {
            $method = 'POST';
        } elseif ($request->isMethod('get')) {
            $method = 'GET';
        } elseif ($request->isMethod('put')) {
            $method = 'PUT';
        } elseif ($request->isMethod('delete')) {
            $method = 'DELETE';
        } else {
            $method = 'OTHER';
        }
        // Save request body and URL to a file for debugging/maintenance purposes
        $dataToSave = [
            'timestamp' => now()->toDateTimeString(),
            'url' => $request->fullUrl(),
            'method' => $method,
            'body' => $request->all()
        ];

        Storage::append('pagseguro_notifications.log', json_encode($dataToSave));

        return response()->json([
            'status' => 'success',
            'message' => 'tt',
            'data' => $dataToSave,
            'errors' => null
        ], 200);
        /*
        if ($notificationType !== 'transaction') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid notification type',
                'data' => null,
                'errors' => ['error' => 'invalid_notification_type']
            ], 400);
        }

        $url = env('PAGSEGURO_ENVIRONMENT') === 'sandbox'
            ? env('PAGSEGURO_BASE_URL_SANDBOX') . "/v3/transactions/notifications/{$notificationCode}"
            : env('PAGSEGURO_BASE_URL') . "/v3/transactions/notifications/{$notificationCode}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer " . config('pagseguro.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transaction details',
                'data' => null,
                'errors' => $response->json()
            ], 400);
        }

        $transaction = $response->json();

        // Process the transaction details
        $reservationId = $transaction['reference'];
        $status = $transaction['status'];
        $amount = $transaction['grossAmount'];

        $reservation = Reservation::find($reservationId);
        if ($reservation) {
            Payments::create([
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'status' => $status,
                'payment_date' => now()
            ]);

            if ($status == 'PAID') {
                $reservation->status = 'paid';
                $reservation->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully.',
                'data' => ['transactionStatus' => $status],
                'errors' => null
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found',
                'data' => null,
                'errors' => ['error' => 'reservation_not_found']
            ], 400);
        }
        */
    }
}
