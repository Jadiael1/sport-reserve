<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreFieldRequest",
 *     type="object",
 *     title="Store Field Request",
 *     required={"name", "location", "type", "hourly_rate"},
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
 *     @OA\Property(property="images", type="array", description="Array of images", @OA\Items(type="string", format="binary", description="Image file"))
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
            'cep' => 'required|string|max:10',
            'district' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'number' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'uf' => 'required|string|max:2',
            'complement' => 'nullable|string|max:255',
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
            'location.required' => 'The field location is required.',
            'location.string' => 'The location must be a string.',
            'location.max' => 'The location may not be greater than 255 characters.',
            'type.required' => 'The field type is required.',
            'hourly_rate.required' => 'The hourly rate is required.',
            'cep.required' => 'The postal code is required.',
            'district.required' => 'The district is required.',
            'address.required' => 'The address is required.',
            'number.required' => 'The number is required.',
            'city.required' => 'The city is required.',
            'uf.required' => 'The state is required.',
            'images.array' => 'The images must be an array.',
            'images.max' => 'You may not upload more than 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image may not be greater than 2048 kilobytes.',
        ];
    }
}
