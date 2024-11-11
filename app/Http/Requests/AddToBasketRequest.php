<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="Add to Basket Request",
 *     description="Request payload for adding a product to the basket",
 *     type="object",
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="The ID of the product to add to the basket",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         description="The quantity of the product to add to the basket",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Optional description for the basket item",
 *         example="This is a special item"
 *     )
 * )
 */
class AddToBasketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }
}
