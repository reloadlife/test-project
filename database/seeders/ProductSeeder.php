<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->count(20)->create([
            'name' => fn() => 'Product ' . fake()->word(),
            'description' => fn() => fake()->paragraph(),
            'price' => fn() => fake()->numberBetween(1000, 50000),
            'stock' => fn() => fake()->numberBetween(1, 1000),
        ]);
    }
}
