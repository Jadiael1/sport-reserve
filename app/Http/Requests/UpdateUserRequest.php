<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     type="object",
 *     title="Update User Request",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the user",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email of the user",
 *         example="john.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="cpf",
 *         type="string",
 *         description="CPF of the user",
 *         example="12345678901"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Phone number of the user",
 *         example="(81) 99999-9999"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Password of the user",
 *         example="password123"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Password confirmation",
 *         example="password123"
 *     ),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         description="Admin status of the user",
 *         example=true
 *     )
 * )
 */
class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');
        return [
            'name' => 'sometimes|required|string|max:255',
            'cpf' => 'sometimes|required|string|max:11|min:11',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'password' => 'sometimes|required|string|min:8',
            'is_admin' => 'sometimes|required|boolean',
        ];
    }
}
