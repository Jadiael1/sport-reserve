<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="FieldImage",
 *     type="object",
 *     title="Field Image",
 *     required={"field_id", "path"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the field image"
 *     ),
 *     @OA\Property(
 *         property="field_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the related field"
 *     ),
 *     @OA\Property(
 *         property="path",
 *         type="string",
 *         description="Path to the image file"
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
class FieldImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_id',
        'path',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
