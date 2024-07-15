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
            'name' => 'sometimes|string|max:100',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:50',
            'hourly_rate' => 'sometimes|numeric|between:0,99999.99',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'nullable|integer|exists:field_images,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        return [
            'name.string' => 'The field name must be a string.',
            'name.max' => 'The field name may not be greater than 100 characters.',
            'location.string' => 'The field location must be a string.',
            'location.max' => 'The field location may not be greater than 255 characters.',
            'type.string' => 'The field type must be a string.',
            'type.max' => 'The field type may not be greater than 50 characters.',
            'hourly_rate.numeric' => 'The hourly rate must be a number.',
            'hourly_rate.between' => 'The hourly rate must be between 0 and 99999.99.',
            'images.array' => 'The images must be an array.',
            'images.max' => 'You may not upload more than 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image may not be greater than 2048 kilobytes.',
            'image_ids.array' => 'The image IDs must be an array.',
            'image_ids.*.integer' => 'Each image ID must be an integer.',
            'image_ids.*.exists' => 'Each image ID must exist in the field_images table.',
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        // Remove null values and null array values
        $cleanedInput = array_filter($input, function ($value) {
            if (is_array($value)) {
                return array_filter($value, function ($item) {
                    return !is_null($item);
                });
            }
            return !is_null($value);
        });

        $this->replace($cleanedInput);
    }
}
