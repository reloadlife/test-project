<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\User;
use Illuminate\Database\Seeder;

class BasketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create baskets for each user
        User::all()->each(function (User $user) {
            Basket::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create([
                    'user_id' => $user->id,
                    'total_price' => fn() => fake()->numberBetween(1000, 100000),
                    'description' => fn() => fake()->sentence(),
                ]);
        });
    }
}
