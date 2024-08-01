<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ReportRequest",
 *     type="object",
 *     required={"start_date", "end_date"},
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         description="Start date for the report"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         description="End date for the report"
 *     ),
 *     @OA\Property(
 *         property="field_id",
 *         type="integer",
 *         description="Field ID (optional)"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="User ID (optional)"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Reservation status (optional)",
 *         enum={"WAITING", "PAID", "CANCELED"}
 *     )
 * )
 */
class ReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'field_id' => 'sometimes|exists:fields,id',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|string|in:WAITING,PAID,CANCELED',
        ];
    }
}
