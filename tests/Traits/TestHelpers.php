<?php

namespace Tests\Traits;

use App\Models\{User, Product, Basket, BasketItem};
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\TestResponse;

trait TestHelpers
{
    /**
     * Create an authenticated user and return user with token
     *
     * @param array $attributes
     * @return array{user: User, token: string}
     */
    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Create and authenticate a user for testing
     *
     * @param array $attributes
     * @return User
     */
    protected function authenticateUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * Create an admin user
     *
     * @return User
     */
    protected function createAdmin(): User
    {
        return User::factory()->create([
            'email' => config('mail.admin.address')
        ]);
    }

    /**
     * Create a product with specified stock and price
     *
     * @param int $stock
     * @param int $price
     * @return Product
     */
    protected function createProduct(int $stock = 10, int $price = 1000): Product
    {
        return Product::factory()->create([
            'stock' => $stock,
            'price' => $price
        ]);
    }

    /**
     * Create a basket with items
     *
     * @param User $user
     * @param int $itemsCount
     * @return Basket
     */
    protected function createBasketWithItems(User $user, int $itemsCount = 1): Basket
    {
        $basket = Basket::factory()
            ->for($user)
            ->create();

        $product = $this->createProduct(10, 10000);

        for ($i = 0; $i < $itemsCount; $i++) {
            $basket->items()->create([
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(1, 5),
                'description' => fake()->sentence(),
            ]);
        }

        return $basket->fresh('items');
    }

    /**
     * Create an expired basket
     *
     * @param User $user
     * @return Basket
     */
    protected function createExpiredBasket(User $user): Basket
    {
        return Basket::factory()
            ->for($user)
            ->create([
                'created_at' => now()->subHours(25)
            ]);
    }

    /**
     * Add product to user's basket
     *
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @return TestResponse
     */
    protected function addToBasket(User $user, Product $product, int $quantity = 1): TestResponse
    {
        return $this->actingAs($user)
            ->postJson('/api/basket/items', [
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
    }

    /**
     * Assert basket is empty
     *
     * @param User $user
     * @return void
     */
    protected function assertBasketIsEmpty(User $user): void
    {
        $response = $this->actingAs($user)->getJson('/api/basket');

        $response->assertJson([
            'items' => []
        ]);
    }

    /**
     * Assert basket contains specific product
     *
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    protected function assertBasketContains(User $user, Product $product, int $quantity = 1): void
    {
        $response = $this->actingAs($user)->getJson('/api/basket');

        $response->assertJson([
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ]
            ]
        ]);
    }

    /**
     * Assert database has product with specific attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function assertProductExists(array $attributes): void
    {
        $this->assertDatabaseHas('products', $attributes);
    }

    /**
     * Assert product stock level
     *
     * @param Product $product
     * @param int $expectedStock
     * @return void
     */
    protected function assertProductStock(Product $product, int $expectedStock): void
    {
        $this->assertEquals(
            $expectedStock,
            $product->fresh()->stock
        );
    }

    /**
     * Make a login request
     *
     * @param string $email
     * @param string $password
     * @return TestResponse
     */
    protected function makeLoginRequest(string $email = 'test@example.com', string $password = 'password'): TestResponse
    {
        return $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password
        ]);
    }

    /**
     * Make a registration request
     *
     * @param array $data
     * @return TestResponse
     */
    protected function makeRegistrationRequest(array $data = []): TestResponse
    {
        $defaultData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        return $this->postJson('/api/register', array_merge($defaultData, $data));
    }
}
