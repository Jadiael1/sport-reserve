<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateReservationRequest",
 *     required={"start_time", "end_time"},
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-06-30T14:00:00Z"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-06-30T15:00:00Z")
 * )
 */
class UpdateReservationRequest extends FormRequest
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
        $reservationId = $this->route('reservation');
        return [
            'start_time' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::unique('reservations', 'start_time')->ignore($reservationId),
                'before:end_time'
            ],
            'end_time' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::unique('reservations', 'end_time')->ignore($reservationId),
                'after:start_time'
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        return [
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in the format Y-m-d H:i:s.',
            'start_time.unique' => 'The start time must be unique.',
            'start_time.before' => 'The start time must be before the end time.',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in the format Y-m-d H:i:s.',
            'end_time.unique' => 'The end time must be unique.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
