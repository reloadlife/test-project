<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BasketItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add items to each basket
        Basket::all()->each(function (Basket $basket) {
            // Get random products
            $products = Product::inRandomOrder()
                ->take(fake()->numberBetween(1, 5))
                ->get();

            // Create basket items for each product
            $products->each(function (Product $product) use ($basket) {
                BasketItem::factory()->create([
                    'basket_id' => $basket->id,
                    'product_id' => $product->id,
                    'quantity' => fake()->numberBetween(1, 5),
                    'description' => fn() => fake()->sentence(),
                ]);
            });
        });
    }
}
