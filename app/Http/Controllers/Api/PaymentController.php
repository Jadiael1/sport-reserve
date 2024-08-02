<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Field;
use App\Models\Reservation;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/payments",
     *     operationId="getPaymentsList",
     *     tags={"Payments"},
     *     summary="Get list of payments",
     *     description="Returns list of payments",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=15),
     *         description="Number of payments per page"
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"reservation_id", "amount", "status", "payment_date", "url", "response", "checkout_id", "self_url", "inactivate_url", "response_payment", "created_at", "updated_at"}
     *         ),
     *         description="Field to sort by"
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}),
     *         description="Sort order: asc or desc"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payments successfully recovered."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="prev_page_url", type="string"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid sort field or sort order",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid sort field or sort order."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve payments.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve payments."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 15);
            $sortBy = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            $validSortFields = [
                'reservation_id',
                'amount',
                'status',
                'payment_date',
                'url',
                'response',
                'checkout_id',
                'self_url',
                'inactivate_url',
                'response_payment',
                'created_at',
                'updated_at'
            ];

            if (!in_array($sortBy, $validSortFields)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid sort field.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            if (!in_array($sortOrder, ['asc', 'desc'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid sort order.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $payments = Payment::with(['reservation.field'])->orderBy($sortBy, $sortOrder)->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Payments successfully recovered.',
                'data' => $payments,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve payments.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/payments/reservations/{id}/pay",
     *     operationId="storePayment",
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
    public function store($id)
    {
        try {
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
                    'name' => $user->name,
                    'tax_id' => $user->cpf,
                    'phone' => array(
                        'country' => '55',
                        'area' => substr($user->phone, 0, 2),
                        'number' => substr($user->phone, 2),
                    )
                ),
                'reference_id' => "{$reservation->field_id}-{$reservation->id}-{$user->id}",
                'customer_modifiable' => true,
                'items' => array(
                    array(
                        'reference_id' => "{$reservation->field_id}-{$reservation->id}-{$user->id}",
                        'name' => 'Reserva ' . ucfirst($field->name),
                        'description' => 'Reserva de uma quadra esportiva',
                        'quantity' => 1,
                        'unit_amount' => $totalAmount,
                    ),
                ),
                'payment_methods' => array(
                    array('type' => 'PIX'),
                    array('type' => 'debit_card'),
                    array('type' => 'credit_card'),
                ),
                'payment_methods_configs' => array(
                    array(
                        'type' => 'credit_card',
                        'config_options' => array(
                            array(
                                'option' => 'installments_limit',
                                'value' => '1'
                            )
                        )
                    )
                ),
                'soft_descriptor' => 'SR_' . ucfirst(str_replace(' ', '_', $field->name)),
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
                Storage::append('pagseguro_success_checkout.json', json_encode($responseData));
                $checkoutId =  $responseData['id'];
                $payLink = collect($responseData['links'])->firstWhere('rel', 'PAY')['href'] ?? null;
                $selfUrl = collect($responseData['links'])->firstWhere('rel', 'SELF')['href'] ?? null;
                $inactivateUrl = collect($responseData['links'])->firstWhere('rel', 'INACTIVATE')['href'] ?? null;

                Payment::create([
                    'reservation_id' => $reservation->id,
                    'amount' => $totalAmount / 100,
                    'status' => 'WAITING',
                    'url' => $payLink,
                    'response' => json_encode($response->json()),
                    'checkout_id' => $checkoutId,
                    'self_url' => $selfUrl,
                    'inactivate_url' => $inactivateUrl,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment link generated successfully.',
                    'data' => array('url' => $payLink),
                    'errors' => null
                ], 200);
            } else {
                Storage::append('pagseguro_fail_checkout.json', json_encode(array(
                    'totalAmount' => $totalAmount,
                    'url' => $url,
                    'token' => $token,
                    'body' => $body,
                    'response_json' => $response->json(),
                )));
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to initiate payment',
                    'data' => null,
                    'errors' => $response->json()
                ], 400);
            }
        } catch (Exception $e) {
            Storage::append('pagseguro_exception_checkout.json', json_encode(array(
                'getMessage' => $e->getMessage()
            )));
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/payments/{id}",
     *     operationId="getPaymentById",
     *     tags={"Payments"},
     *     summary="Get payment by ID",
     *     description="Returns a specific payment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the payment"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully recovered.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment successfully recovered."),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Payment not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $payments = Payment::with(['reservation.field'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Payment successfully recovered.',
                'data' => $payments,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Patch(
     *     path="/api/v1/payments/{id}",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     *     summary="Update payment",
     *     description="Updates a specific payment's details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the payment to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", example=200.50, description="Amount of the payment"),
     *             @OA\Property(property="status", type="string", example="PAID", description="Status of the payment"),
     *             @OA\Property(property="payment_date", type="string", format="date-time", example="2024-07-01T18:14:17.702Z", description="Date of the payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data provided.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid data provided."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Payment not found."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */

    public function update(UpdatePaymentRequest $request, string $id)
    {
        $validatedData = $request->validated();
        try {
            $payment = Payment::findOrFail($id);
            $payment->update($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully.',
                'data' => $payment,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/payments/{id}",
     *     operationId="deletePayment",
     *     tags={"Payments"},
     *     summary="Delete payment",
     *     description="Deletes a specific payment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the payment to delete"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully deleted.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment successfully deleted."),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Payment not found."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete payment.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to delete payment."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $paymentToDelete = $payment;
            $payment->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Payment successfully deleted.',
                'data' => $paymentToDelete,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete payment.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
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
            Storage::append('pagseguro_notifications.json', json_encode($request->all()));
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid notification data.',
                'data' => null,
                'errors' => array('error' => 'Invalid charges data')
            ], 400);
        }

        $charge = $data['charges'][0];
        if (!isset($charge['id'])) {
            Storage::append('pagseguro_notifications_id.json', json_encode($request->all()));
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
                Storage::append('pagseguro_notifications_reference_id.json', json_encode($request->all()));
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
                Storage::append('pagseguro_notifications_parts.json', json_encode($request->all()));
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reservation not found.',
                    'data' => null,
                    'errors' => array('error' => 'Reservation not found')
                ], 400);
            }

            $payment = $reservation->payments()->where('reservation_id', $reservation->id)->where('status', 'WAITING')->first();
            if (!$payment) {
                Storage::append('pagseguro_notifications_payment.json', json_encode($request->all()));
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found.',
                    'data' => null,
                    'errors' => array('error' => 'Payment not found')
                ], 400);
            }

            if ($responseData['status'] == 'PAID') {
                $newRequest = new Request();
                $this->toggleCheckoutStatus($newRequest, $payment->checkout_id);
                $payment->update([
                    'amount' => $responseData['amount']['value'] / 100, // assuming the amount is in cents
                    'status' => $responseData['status'],
                    'charge_id' => $responseData['id'],
                    'payment_date' => Carbon::parse($responseData['paid_at']),
                    'response_payment' => json_encode($responseData),
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
        Storage::append('pagseguro_notifications_error.json', json_encode($request->all()));
        return response()->json([
            'status' => 'error',
            'message' => 'Payment not completed.',
            'data' => null,
            'errors' => array('error' => 'Payment status is not PAID')
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/checkouts/{checkout_id}/toggle",
     *     operationId="toggleCheckoutStatus",
     *     tags={"Payments"},
     *     summary="Toggle the status of a checkout",
     *     description="Activates or inactivates a checkout based on the provided flag or toggles its current state",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="checkout_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID of the checkout"
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activate", "inactivate"}),
     *         description="Action to perform: activate or inactivate"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkout status updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Checkout status updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="checkout_id", type="string"),
     *                 @OA\Property(property="current_status", type="string")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid action provided or reservation has already started.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid action provided or reservation has already started."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Checkout not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Checkout not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function toggleCheckoutStatus(Request $request, string $checkout_id)
    {
        try {
            $action = $request->query('action');

            if (!in_array($action, ['activate', 'inactivate', null])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid action provided.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $now = Carbon::now('America/Recife');
            $payment = Payment::with(['reservation'])->where('checkout_id', $checkout_id)->firstOrFail();
            $currentStatus = $payment->status;

            if ($payment->reservation->start_time <= $now) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot change status for a reservation that has already started.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            // Determine the toggle action if not explicitly provided
            $toggleAction = $action ?? ($currentStatus === 'WAITING' ? 'inactivate' : 'activate');
            $toggleUrl = $toggleAction === 'activate'
                ? $payment->self_url . '/activate'
                : $payment->inactivate_url;

            $token = config('pagseguro.environment') === 'sandbox' ? config('pagseguro.tokenSandBox') : config('pagseguro.token');
            $response = Http::withHeaders([
                'Authorization' => "Bearer " . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($toggleUrl);

            if ($response->successful()) {
                // Update the payment status based on the toggle action
                $payment->update([
                    'status' => $toggleAction === 'activate' ? 'WAITING' : 'INACTIVE'
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Checkout status updated successfully.',
                    'data' => [
                        'checkout_id' => $checkout_id,
                        'current_status' => $payment->status
                    ],
                    'errors' => null
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update checkout status.',
                'data' => null,
                'errors' => $response->json()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Checkout not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/v1/payments/{id}/refund",
     *     operationId="refundPayment",
     *     tags={"Payments"},
     *     summary="Refund a payment",
     *     description="Processes a refund for a specific payment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the payment to refund"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully refunded.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment successfully refunded."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_id", type="integer"),
     *                 @OA\Property(property="status", type="string")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid payment status or refund amount.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid payment status or refund amount."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Payment not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to process refund.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to process refund."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function refundPayment(string $charge_id)
    {
        try {
            $payment = Payment::where('charge_id', $charge_id)->firstOrFail();

            if ($payment->status !== 'PAID') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payment status for refund.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $amountInCents = (int) ($payment->amount * 100);

            $url = config('pagseguro.environment') === 'sandbox' ?
                config('pagseguro.baseUrlSandBox') . "/charges/{$payment->charge_id}/cancel" :
                config('pagseguro.baseUrl') . "/charges/{$payment->charge_id}/cancel";

            $token = config('pagseguro.environment') === 'sandbox' ?
                config('pagseguro.tokenSandBox') :
                config('pagseguro.token');

            $response = Http::withHeaders([
                'Authorization' => "Bearer " . $token,
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])->post($url, [
                'amount' => ['value' => $amountInCents]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $payment->update([
                    'status' => 'REFUNDED'
                ]);

                $payment->reservation->update([
                    'status' => $responseData['status']
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment successfully refunded.',
                    'data' => [
                        'payment_id' => $payment->id,
                        'status' => $payment->status
                    ],
                    'errors' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process refund.',
                    'data' => null,
                    'errors' => $response->json()
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found or failed to process refund.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
