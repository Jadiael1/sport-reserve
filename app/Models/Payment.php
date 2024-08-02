<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     title="Payment",
 *     required={"reservation_id", "amount", "status", "payment_date", "url"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the payment"
 *     ),
 *     @OA\Property(
 *         property="reservation_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the related reservation"
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="Amount of the payment"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the payment"
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         type="string",
 *         format="date-time",
 *         description="Date of the payment"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         description="URL of the payment"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     )
 * )
 */
class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'reservation_id',
        'amount',
        'status',
        'payment_date',
        'url',
        'response',
        'checkout_id',
        'charge_id',
        'self_url',
        'inactivate_url',
        'response_payment',
    ];

    // protected $hidden = ['response', 'checkout_id', 'self_url', 'inactivate_url', 'response_payment'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
