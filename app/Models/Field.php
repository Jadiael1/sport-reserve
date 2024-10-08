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
 *     @OA\Property(property="id", type="integer", format="int64", description="ID of the field"),
 *     @OA\Property(property="name", type="string", description="Name of the field"),
 *     @OA\Property(property="location", type="object", description="Location of the field with latitude and longitude"),
 *     @OA\Property(property="type", type="string", description="Type of the field"),
 *     @OA\Property(property="hourly_rate", type="number", format="float", description="Hourly rate for renting the field"),
 *     @OA\Property(property="status", type="string", description="Field status: active or inactive"),
 *     @OA\Property(property="cep", type="string", description="Field postal code"),
 *     @OA\Property(property="district", type="string", description="Field district"),
 *     @OA\Property(property="address", type="string", description="Field address"),
 *     @OA\Property(property="number", type="string", description="Field address number"),
 *     @OA\Property(property="city", type="string", description="Field city"),
 *     @OA\Property(property="uf", type="string", description="Field state"),
 *     @OA\Property(property="complement", type="string", description="Field address complement", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
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
        'status',
        'cep',
        'district',
        'address',
        'number',
        'city',
        'uf',
        'complement'
    ];

    public function images()
    {
        return $this->hasMany(FieldImage::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function availabilities()
    {
        return $this->hasMany(FieldAvailability::class);
    }
}
