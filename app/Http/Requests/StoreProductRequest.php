<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="Store Product Request",
 *     description="Request payload for creating a new product",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the product",
 *         example="Product Name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="The description of the product",
 *         example="This is a description of the product."
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="integer",
 *         description="The price of the product",
 *         example=19.99
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         description="The stock quantity of the product",
 *         example=100
 *     )
 * )
 */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
