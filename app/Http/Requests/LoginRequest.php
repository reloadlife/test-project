<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     title="Login Request",
 *     description="Request payload for logging in a user",
 *     type="object",
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
 *     )
 * )
 */
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
