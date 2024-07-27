<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Reservation;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="UpdateReservationRequest",
 *     type="object",
 *     title="Update Reservation Request",
 *     required={"field_id", "start_time", "end_time"},
 *     @OA\Property(
 *         property="field_id",
 *         type="integer",
 *         description="ID of the field",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="start_time",
 *         type="string",
 *         format="date-time",
 *         description="Start time of the reservation",
 *         example="2023-06-30T14:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="end_time",
 *         type="string",
 *         format="date-time",
 *         description="End time of the reservation",
 *         example="2023-06-30T15:00:00Z"
 *     )
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
        $reservationId = $this->route('reservation') ?? $this->route('id');

        return [
            'field_id' => 'sometimes|required|exists:fields,id',
            'start_time' => [
                'sometimes',
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidDateFormat($value)) {
                        $fail('The start time must be in the format Y-m-d H:i:s or Y-m-d\TH:i:s.v\Z or Y-m-d\TH:i:s or Y-m-d\TH:i.');
                    }
                },
                'before:end_time',
                function ($attribute, $value, $fail) use ($reservationId) {
                    if ($this->isOverlapping($value, $this->end_time, $this->field_id, $reservationId)) {
                        $fail('The reservation times overlap with an existing reservation.');
                    }
                }
            ],
            'end_time' => [
                'sometimes',
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidDateFormat($value)) {
                        $fail('The end time must be in the format Y-m-d H:i:s or Y-m-d\TH:i:s.v\Z or Y-m-d\TH:i:s or Y-m-d\TH:i.');
                    }
                },
                'after:start_time',
            ],
        ];
    }

    /**
     * Check if the reservation times overlap with any existing reservation.
     */
    protected function isOverlapping($startTime, $endTime, $fieldId, $reservationId = null)
    {
        $this->cleanupPendingReservations($fieldId, $startTime, $endTime);

        return Reservation::where('field_id', $fieldId)
            ->where('status', '!=', 'CANCELED')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $startTime)
                            ->where('end_time', '>', $endTime);
                    });
            })
            ->when($reservationId, function ($query) use ($reservationId) {
                $query->where('id', '!=', $reservationId);
            })
            ->exists();
    }

    /**
     * Cleanup pending reservations older than 30 minutes.
     */
    protected function cleanupPendingReservations($fieldId, $startTime, $endTime)
    {
        $thresholdTime = Carbon::now('America/Recife')->subMinutes(30);

        $pendingReservations = Reservation::where('field_id', $fieldId)
            ->where('status', 'WAITING')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $startTime)
                            ->where('end_time', '>', $endTime);
                    });
            })
            ->where('created_at', '<', $thresholdTime)
            ->get();

        foreach ($pendingReservations as $reservation) {
            $reservation->update(['status' => 'CANCELED']);
        }
    }

    /**
     * Check if the given date is in a valid format.
     */
    protected function isValidDateFormat($date)
    {
        $formats = ['Y-m-d H:i:s', 'Y-m-d\TH:i:s.v\Z', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i'];
        foreach ($formats as $format) {
            if (\DateTime::createFromFormat($format, $date) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        return [
            'field_id.required' => 'The field ID is required.',
            'field_id.exists' => 'The selected field ID is invalid.',
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in the format Y-m-d H:i:s.',
            'start_time.before' => 'The start time must be before the end time.',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in the format Y-m-d H:i:s.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
