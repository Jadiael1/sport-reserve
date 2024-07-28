<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="FieldAvailability",
 *     type="object",
 *     title="Field Availability",
 *     required={"field_id", "day_of_week", "start_time", "end_time"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the availability"
 *     ),
 *     @OA\Property(
 *         property="field_id",
 *         type="integer",
 *         description="ID of the field"
 *     ),
 *     @OA\Property(
 *         property="day_of_week",
 *         type="string",
 *         description="Day of the week"
 *     ),
 *     @OA\Property(
 *         property="start_time",
 *         type="string",
 *         format="time",
 *         description="Start time"
 *     ),
 *     @OA\Property(
 *         property="end_time",
 *         type="string",
 *         format="time",
 *         description="End time"
 *     )
 * )
 */
class FieldAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_id',
        'day_of_week',
        'start_time',
        'end_time'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
