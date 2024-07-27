<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Reservation;
use App\Models\Payment;
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Reservation already paid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Reservation already paid"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function initiatePayment(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->status === 'PAID') {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation already paid',
                'data' => null,
                'errors' => null
            ], 422);
        }

        $payment = $reservation->payments()->where('reservation_id', $id)->where('status', 'WAITING')->first();
        if ($payment) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment link generated successfully..',
                'data' => array('url' => $payment->url),
                'errors' => null
            ], 200);
        }

        $user = $reservation->user;
        $field = Field::findOrFail($reservation->field_id);

        $startTime = Carbon::parse($reservation->start_time);
        $endTime = Carbon::parse($reservation->end_time);

        $durationInMinutes = $startTime->diffInMinutes($endTime);
        $pricePerMinute = $reservation->field->hourly_rate / 60;
        $totalAmount = round($durationInMinutes * $pricePerMinute * 100, 2);

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
            'soft_descriptor' => 'sport-reserve',
        );
        $appUrl = env('APP_URL');
        if ($appUrl && strpos($appUrl, 'localhost') === false) {
            $body['redirect_url'] = env('SAP_URL');
            $body['return_url'] = env('SAP_URL');
            $body['payment_notification_urls'] = array($appUrl . "/api/v1/payments/notify");
        }
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

            Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $totalAmount / 100,
                'status' => 'WAITING',
                'url' => $payLink
            ]);


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
     *     summary="Handle payment notification from PagSeguro",
     *     description="Processes payment notifications sent by PagSeguro for various payment methods.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ORDE_797D6FDC-3E93-4A13-AC03-315AD674ACC0"),
     *             @OA\Property(property="reference_id", type="string", example="1-9-2"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-01T18:13:58.341-03:00"),
     *             @OA\Property(property="customer", type="object",
     *                 @OA\Property(property="name", type="string", example="name surname"),
     *                 @OA\Property(property="email", type="string", example="email@email.com"),
     *                 @OA\Property(property="tax_id", type="string", example="47756883080"),
     *                 @OA\Property(property="phones", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="type", type="string", example="MOBILE"),
     *                         @OA\Property(property="country", type="string", example="55"),
     *                         @OA\Property(property="area", type="string", example="81"),
     *                         @OA\Property(property="number", type="string", example="995207889")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="reference_id", type="string", example="1-9-2"),
     *                     @OA\Property(property="name", type="string", example="Reserva campo1"),
     *                     @OA\Property(property="quantity", type="integer", example=1),
     *                     @OA\Property(property="unit_amount", type="integer", example=2500)
     *                 )
     *             ),
     *             @OA\Property(property="charges", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="CHAR_0259A44A-EB88-4139-98F9-55B96659B7A5"),
     *                     @OA\Property(property="reference_id", type="string", example="1-9-2"),
     *                     @OA\Property(property="status", type="string", example="PAID"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-01T18:14:15.698-03:00"),
     *                     @OA\Property(property="paid_at", type="string", format="date-time", example="2024-07-01T18:14:17.702-03:00"),
     *                     @OA\Property(property="amount", type="object",
     *                         @OA\Property(property="value", type="integer", example=2500),
     *                         @OA\Property(property="currency", type="string", example="BRL"),
     *                         @OA\Property(property="summary", type="object",
     *                             @OA\Property(property="total", type="integer", example=2500),
     *                             @OA\Property(property="paid", type="integer", example=2500),
     *                             @OA\Property(property="refunded", type="integer", example=0)
     *                         )
     *                     ),
     *                     @OA\Property(property="payment_response", type="object",
     *                         @OA\Property(property="code", type="string", example="20000"),
     *                         @OA\Property(property="message", type="string", example="SUCESSO")
     *                     ),
     *                     @OA\Property(property="payment_method", type="object",
     *                         @OA\Property(property="type", type="string", example="PIX"),
     *                         @OA\Property(property="pix", type="object",
     *                             @OA\Property(property="notification_id", type="string", example="NTF_A0AC3061-6449-47F3-8066-BBC1C91B3DF7"),
     *                             @OA\Property(property="end_to_end_id", type="string", example="99e16d24c9aa46d5ae63fb719b16d581"),
     *                             @OA\Property(property="holder", type="object",
     *                                 @OA\Property(property="name", type="string", example="API-PIX Payer Mock"),
     *                                 @OA\Property(property="tax_id", type="string", example="***931180**")
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="notification_urls", type="array",
     *                 @OA\Items(type="string", example="https://api-sport-reserve.juvhost.com/api/v1/payments/notify")
     *             ),
     *             @OA\Property(property="links", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="rel", type="string", example="SELF"),
     *                     @OA\Property(property="href", type="string", example="https://sandbox.api.pagseguro.com/orders/ORDE_797D6FDC-3E93-4A13-AC03-315AD674ACC0"),
     *                     @OA\Property(property="media", type="string", example="application/json"),
     *                     @OA\Property(property="type", type="string", example="GET")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment notification processed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment notification processed successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservation_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="paid")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid notification data or other error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid notification data."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Invalid charges data")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed to get payment",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to get payment"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Failed to get payment"),
     *                 @OA\Property(property="message", type="string", example="Payment status is not PAID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Payment not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Payment not found")
     *             )
     *         )
     *     )
     * )
     */
    public function paymentNotification(Request $request)
    {
        $data = $request->all();

        if (!isset($data['charges']) || !is_array($data['charges'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid notification data.',
                'data' => null,
                'errors' => array('error' => 'Invalid charges data')
            ], 400);
        }

        $charge = $data['charges'][0];
        if (!isset($charge['id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid notification data.',
                'data' => null,
                'errors' => array('error' => 'Missing required charge fields')
            ], 400);
        }

        $url = config('pagseguro.environment') === 'sandbox' ? config('pagseguro.baseUrlSandBox') . "/charges/" . $charge['id'] : config('pagseguro.baseUrl') . "/charges/" . $charge['id'];
        $token = config('pagseguro.environment') === 'sandbox' ? config('pagseguro.tokenSandBox') : config('pagseguro.token');
        $response = Http::withHeaders([
            'Authorization' => "Bearer " . $token,
            'Accept' => '*/*',
        ])->get($url);

        if ($response->successful()) {
            $responseData = $response->json();

            if (!isset($responseData['reference_id'], $responseData['status'], $responseData['paid_at'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid notification data.',
                    'data' => null,
                    'errors' => array('error' => 'Missing required charge fields')
                ], 400);
            }

            $parts = explode('-', $responseData['reference_id']);
            $reservation = Reservation::where('id', $parts[1])->first();
            if (!$reservation || count($parts) !== 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reservation not found.',
                    'data' => null,
                    'errors' => array('error' => 'Reservation not found')
                ], 400);
            }

            $payment = $reservation->payments()->where('reservation_id', $reservation->id)->where('status', 'WAITING')->first();
            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found.',
                    'data' => null,
                    'errors' => array('error' => 'Payment not found')
                ], 400);
            }

            if ($responseData['status'] == 'PAID') {
                $payment->update([
                    'amount' => $responseData['amount']['value'] / 100, // assuming the amount is in cents
                    'status' => $responseData['status'],
                    'payment_date' => Carbon::parse($responseData['paid_at'])
                ]);

                $reservation->status = $responseData['status'];
                $reservation->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment notification processed successfully.',
                    'data' => array('reservation_id' => $reservation->id, 'status' => $reservation->status),
                    'errors' => null
                ], 200);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get payment',
                'data' => null,
                'errors' => $response->json()
            ], 404);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Payment not completed.',
            'data' => null,
            'errors' => array('error' => 'Payment status is not PAID')
        ], 400);
    }
}
