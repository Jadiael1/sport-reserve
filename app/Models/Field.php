<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Field",
 *     type="object",
 *     title="Field",
 *     required={"name", "location", "type", "hourly_rate"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the field"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the field"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         description="Location of the field"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of the field"
 *     ),
 *     @OA\Property(
 *         property="hourly_rate",
 *         type="number",
 *         format="float",
 *         description="Hourly rate for renting the field"
 *     )
 * )
 */
class Field extends Model
{
    use HasFactory;

    protected $table = 'fields';

    protected $fillable = [
        'name',
        'location',
        'type',
        'hourly_rate',
    ];
}
