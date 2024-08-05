<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateFieldRequest",
 *     type="object",
 *     title="Update Field Request",
 *     @OA\Property(property="name", type="string", description="Name of the field", example="Soccer Field"),
 *     @OA\Property(property="location", type="object", description="Location of the field", example={"lat": -8.6855317, "lng": -35.5914402}),
 *     @OA\Property(property="type", type="string", description="Type of the field", example="Soccer"),
 *     @OA\Property(property="hourly_rate", type="number", format="float", description="Hourly rate for renting the field", example=50.00),
 *     @OA\Property(property="cep", type="string", description="Postal code", example="12345-678"),
 *     @OA\Property(property="district", type="string", description="District", example="Centro"),
 *     @OA\Property(property="address", type="string", description="Address", example="Rua ABC"),
 *     @OA\Property(property="number", type="string", description="Address number", example="123"),
 *     @OA\Property(property="city", type="string", description="City", example="São Paulo"),
 *     @OA\Property(property="uf", type="string", description="State", example="SP"),
 *     @OA\Property(property="complement", type="string", description="Address complement", nullable=true, example="Apt 101"),
 *     @OA\Property(property="images", type="array", description="Array of image files", @OA\Items(type="string", format="binary", description="Image file")),
 *     @OA\Property(property="image_ids", type="array", description="Array of image IDs", @OA\Items(type="integer", description="ID of the image"))
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
            'cep' => 'sometimes|string|max:10',
            'district' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:255',
            'number' => 'sometimes|string|max:10',
            'city' => 'sometimes|string|max:100',
            'uf' => 'sometimes|string|max:2',
            'complement' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:active,inactive',
            'images' => 'sometimes|array|max:5',
            'images.*' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'image_ids' => 'sometimes|array',
            'image_ids.*' => 'sometimes|integer|exists:field_images,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.sometimes' => 'The name field is optional.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 100 characters.',
            'location.sometimes' => 'The location field is optional.',
            'location.string' => 'The location must be a string.',
            'location.max' => 'The location may not be greater than 255 characters.',
            'type.sometimes' => 'The type field is optional.',
            'type.string' => 'The type must be a string.',
            'type.max' => 'The type may not be greater than 50 characters.',
            'hourly_rate.sometimes' => 'The hourly rate field is optional.',
            'hourly_rate.numeric' => 'The hourly rate must be a number.',
            'hourly_rate.between' => 'The hourly rate must be between 0 and 99999.99.',
            'status.sometimes' => 'The status field is optional.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be either "active" or "inactive".',
            'images.sometimes' => 'The images field is optional.',
            'images.array' => 'The images must be an array.',
            'images.max' => 'You may not upload more than 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image may not be greater than 2048 kilobytes.',
            'image_ids.sometimes' => 'The image IDs field is optional.',
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
