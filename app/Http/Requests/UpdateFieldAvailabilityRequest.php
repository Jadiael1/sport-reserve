<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateFieldAvailabilityRequest",
 *     type="object",
 *     @OA\Property(
 *         property="day_of_week",
 *         type="string",
 *         description="Day of the week",
 *         example="Monday"
 *     ),
 *     @OA\Property(
 *         property="start_time",
 *         type="string",
 *         format="time",
 *         description="Start time",
 *         example="08:00"
 *     ),
 *     @OA\Property(
 *         property="end_time",
 *         type="string",
 *         format="time",
 *         description="End time",
 *         example="12:00"
 *     )
 * )
 */
class UpdateFieldAvailabilityRequest extends FormRequest
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
            'day_of_week' => 'sometimes|required|string|max:10',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'day_of_week.sometimes' => 'The day of the week field is optional.',
            'day_of_week.required' => 'The day of the week is required.',
            'day_of_week.string' => 'The day of the week must be a string.',
            'day_of_week.max' => 'The day of the week may not be greater than 10 characters.',
            'start_time.sometimes' => 'The start time field is optional.',
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in the format H:i:s.',
            'end_time.sometimes' => 'The end time field is optional.',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in the format H:i:s.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
