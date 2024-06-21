<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreFieldRequest",
 *     type="object",
 *     title="Store Field Request",
 *     required={"name", "location", "type", "hourly_rate"},
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
class StoreFieldRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'hourly_rate' => 'required|numeric|between:0,99999.99',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        return [
            'name.required' => 'The field name is required.',
            'name.string' => 'The field name must be a string.',
            'name.max' => 'The field name may not be greater than 100 characters.',
            'location.required' => 'The field location is required.',
            'location.string' => 'The field location must be a string.',
            'location.max' => 'The field location may not be greater than 255 characters.',
            'type.required' => 'The field type is required.',
            'type.string' => 'The field type must be a string.',
            'type.max' => 'The field type may not be greater than 50 characters.',
            'hourly_rate.required' => 'The hourly rate is required.',
            'hourly_rate.numeric' => 'The hourly rate must be a number.',
            'hourly_rate.between' => 'The hourly rate must be between 0 and 99999.99.',
        ];
    }
}
