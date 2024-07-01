<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Payments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PagSeguro\Configuration\Configure;
use PagSeguro\Domains\Requests\Payment as PagSeguroPayment;
use PagSeguro\Library;
use PagSeguro\Domains\Requests\DirectPayment\Pix as PagSeguroPix;

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
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment URL generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url", type="string", example="https://pagseguro.uol.com.br/checkout/v2/payment.html?code=EXAMPLE-CODE")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Unexpected response from PagSeguro or other error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Unexpected response from PagSeguro")
     *             )
     *         )
     *     )
     * )
     */
    public function initiatePayment(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = $reservation->user;
        try {
            Library::initialize();
            Library::cmsVersion()->setName("sport-reserve")->setRelease("0.0.1");
            Library::moduleVersion()->setName("sport-reserve")->setRelease("0.0.1");

            $pagSeguro = new PagSeguroPayment();
            $pagSeguro->setReference($reservation->id);


            $pagSeguro->addItems()->withParameters(
                '0001',
                'Reservation for field ' . $reservation->field_id,
                1,
                (float) $reservation->field->hourly_rate
            );
            // $pagSeguro->setSender()->setName($user->name);
            $pagSeguro->setSender()->setEmail($user->email);
            $pagSeguro->setCurrency('BRL');
            $pagSeguro->setRedirectUrl(env('SAP_URL'));
            $pagSeguro->setSender()->setDocument()->withParameters(
                'CPF',
                $user->cpf
            );

            $onlyCheckoutCode = true;
            Configure::setEnvironment(config('pagseguro.environment'));
            Configure::setAccountCredentials(config('pagseguro.email'), config('pagseguro.token'));
            Configure::setApplicationCredentials(config('pagseguro.appId'), config('pagseguro.appKey'));

            $accountCredentials = Configure::getAccountCredentials();
            $applicationCredentials = Configure::getAccountCredentials();

            $result = $pagSeguro->register($accountCredentials);


            if (is_string($result) && strlen($result)) {
                return response()->json([
                    'status' => 'success',
                    'message' => '',
                    'data' => array('url' => $result),
                    'errors' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => '',
                    'data' => null,
                    'errors' => array('error' => 'Unexpected response from PagSeguro')
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '',
                'data' => $e,
                'errors' => array('error' => 'Unexpected response from PagSeguro', 'message' => $e->getMessage())
            ], 400);
        }







        // $credentials = Configure::setEnvironment(config('pagseguro.environment'))
        //     ->setAccountCredentials(config('pagseguro.email'), config('pagseguro.token'))
        //     ->setAppId(config('pagseguro.appId'))
        //     ->setAppKey(config('pagseguro.appKey'));



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
        $notificationCode = $request->notificationCode;

        $credentials = Configure::getAccountCredentials();
        $response = \PagSeguro\Services\Transactions\Notification::check(
            $credentials,
            $notificationCode
        );

        $reservation = Reservation::find($response->getReference());

        Payments::create([
            'reservation_id' => $reservation->id,
            'amount' => $response->getGrossAmount(),
            'status' => $response->getStatus(),
            'payment_date' => $response->getDate()
        ]);

        if ($response->getStatus() == 3) { // 3 = Pago
            $reservation->status = 'paid';
            $reservation->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => array('message' => 'Payment notification processed.'),
            'errors' => null
        ], 200);
    }
}
