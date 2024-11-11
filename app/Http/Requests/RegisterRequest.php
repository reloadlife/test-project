<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="Register Request",
 *     description="Request payload for registering a new user",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the user",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The email address of the user",
 *         example="johndoe@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="The password of the user",
 *         example="password123"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         description="The confirmation of the user's password",
 *         example="password123"
 *     )
 * )
 */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
