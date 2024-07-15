<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateFieldRequest",
 *     type="object",
 *     title="Update Field Request",
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
class UpdateFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'location' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:50',
            'hourly_rate' => 'sometimes|required|numeric|between:0,99999.99',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
            'images.array' => 'The images must be an array.',
            'images.max' => 'You may not upload more than 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image may not be greater than 2048 kilobytes.',
        ];
    }
}
