<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToBasketRequest;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Basket",
 *     description="API Endpoints for shopping basket management"
 * )
 */
class BasketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/basket",
     *     tags={"Basket"},
     *     summary="Get current user's basket",
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="User's current basket",
     *         @OA\JsonContent(ref="#/components/schemas/Basket")
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $basket = $this->getCurrentBasket();
        $basket->load(['items', 'items.product',]);
        return response()->json($basket);
    }

    /**
     * @OA\Post(
     *     path="/api/basket/items",
     *     tags={"Basket"},
     *     summary="Add item to basket",
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddToBasketRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item added to basket",
     *         @OA\JsonContent(ref="#/components/schemas/BasketItem")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or duplicate product"
     *     )
     * )
     */
    public function addItem(AddToBasketRequest $request): JsonResponse
    {
        $basket = $this->getCurrentBasket();
        $product = Product::findOrFail($request->product_id);

        // Check if product already exists in basket
        // if it exists, we increase the Quantity and check if it meets stock limitations
        $existingItem = $basket->items()->where('product_id', $product->id)->first();
        if ($existingItem) {
            if ($product->stock < ($existingItem->quantity + $request->quantity)) {
                $total = $existingItem->quantity + $request->quantity;
                return response()->json([
                    'message' => "Requested quantity {$total} is not available, you already have {$existingItem->quantity} in your basket"
                ], 422);
            }

            $existingItem->increment('quantity', $request->quantity);
            $existingItem->save();
            return response()->json($existingItem->load('product'), 201);
        }

        // Check product stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => "Requested quantity {$request->quantity} is not available"
            ], 422);
        }

        $basketItem = $basket->items()->create([
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'description' => $request->description ?? $product->description,
        ]);

        return response()->json($basketItem->load('product'), 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/basket/items/{id}",
     *     tags={"Basket"},
     *     summary="Remove item from basket",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Item removed from basket"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     )
     * )
     */
    public function removeItem(BasketItem $item): JsonResponse
    {
        if ($item->basket->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();
        return response()->json(null, 204);
    }

    /**
     * Get or create current basket for the user
     */
    private function getCurrentBasket(): Basket
    {
        return Basket::firstOrCreate(
            [
                'user_id' => auth()->id(),
            ],
            [
                'description' => 'Shopping basket for ' . auth()->user()->name,
                'total_price' => 0
            ]
        );
    }
}
