<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreUserRequest",
 *     type="object",
 *     title="Store User Request",
 *     required={"name", "email", "cpf", "phone", "password"},
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
class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:users,cpf',
            'phone' => 'required|string|max:15|unique:users,phone',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'sometimes|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'cpf.required' => 'The CPF is required.',
            'cpf.string' => 'The CPF must be a string.',
            'cpf.size' => 'The CPF must be exactly 11 characters.',
            'cpf.unique' => 'The CPF has already been taken.',
            'phone.required' => 'The phone number is required.',
            'phone.string' => 'The phone number must be a string.',
            'phone.max' => 'The phone number may not be greater than 15 characters.',
            'phone.unique' => 'The phone number has already been taken.',
            'email.required' => 'The email is required.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'is_admin.boolean' => 'The is_admin field must be true or false.',
        ];
    }
}
