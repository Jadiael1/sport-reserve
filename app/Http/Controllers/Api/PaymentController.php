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

                $payment->reservation->update([
                    'status' => $toggleAction === 'activate' ? 'WAITING' : 'CANCELED'
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
