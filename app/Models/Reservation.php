<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     required={"user_id", "start_time", "end_time"},
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
 *     )
 * )
 */
class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time'
    ];
}
