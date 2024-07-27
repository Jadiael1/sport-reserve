<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     required={"user_id", "field_id", "start_time", "end_time", "status"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the reservation"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user"
 *     ),
 *     @OA\Property(
 *         property="field_id",
 *         type="integer",
 *         description="ID of the field"
 *     ),
 *     @OA\Property(
 *         property="start_time",
 *         type="string",
 *         format="date-time",
 *         description="Start time of the reservation"
 *     ),
 *     @OA\Property(
 *         property="end_time",
 *         type="string",
 *         format="date-time",
 *         description="End time of the reservation"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the reservation"
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
class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'field_id',
        'start_time',
        'end_time',
        'status'
    ];

    /**
     * Get the field that owns the reservation.
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
