<?php

namespace App\Http\Controllers\Documentation;

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
class PaymentDocumentation {}
