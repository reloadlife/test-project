<?php

namespace Tests\Feature;

use App\Models\{User, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Log, Notification};
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ShoppingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Log::info('Starting shopping flow test setup');

        try {
            Notification::fake();

            $this->admin = $this->createAdmin();

            $auth = $this->createAuthenticatedUser([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
            $this->user = $auth['user'];

            $this->product = $this->createProduct();

            Log::info('Test setup completed', [
                'admin_id' => $this->admin->id,
                'user_id' => $this->user->id,
                'product_id' => $this->product->id
            ]);
        } catch (\Exception $e) {
            Log::error('Setup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function test_user_registration_flow(): void
    {
        Log::info('Testing user registration flow');

        $response = $this->makeRegistrationRequest([
            'name' => 'New User',
            'email' => 'new@example.com'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);

        Log::info('User registration test completed');
    }

    public function test_user_login_flow(): void
    {
        Log::info('Testing user login flow');

        $response = $this->makeLoginRequest('test@example.com', 'password');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);

        Log::info('User login test completed');
    }

    public function test_add_product_to_basket_flow(): void
    {
        Log::info('Testing add to basket flow');

        $response = $this->addToBasket($this->user, $this->product, 2);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'basket_id',
                'product_id',
                'quantity',
                'product' => [
                    'id',
                    'name',
                    'price'
                ]
            ]);

        $this->assertBasketContains($this->user, $this->product, 2);
        Log::info('Add to basket test completed');
    }

    public function test_view_basket_flow(): void
    {
        Log::info('Testing view basket flow');

        $basket = $this->createBasketWithItems($this->user);

        $response = $this->actingAs($this->user)->getJson('/api/basket');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'user_id',
                'total_price',
                'items' => [
                    '*' => [
                        'id',
                        'product_id',
                        'quantity',
                        'product'
                    ]
                ]
            ]);

        Log::info('View basket test completed');
    }

    public function test_duplicate_product_prevention_flow(): void
    {
        Log::info('Testing duplicate product prevention flow');

        // Add first item
        $this->addToBasket($this->user, $this->product, 2);

        // Try to add duplicate
        $response = $this->addToBasket($this->user, $this->product, 1);

        $response->assertStatus(201)
            ->assertJson([
                'quantity' => 3
            ]);

        Log::info('Duplicate product prevention test completed');
    }

    public function test_remove_basket_item_flow(): void
    {
        Log::info('Testing remove basket item flow');

        $addResponse = $this->addToBasket($this->user, $this->product, 2);
        $itemId = $addResponse->json('id');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/basket/items/{$itemId}");

        $response->assertStatus(204);
        $this->assertBasketIsEmpty($this->user);

        Log::info('Remove basket item test completed');
    }

    public function test_complete_shopping_flow(): void
    {
        Log::info('Testing complete shopping flow');

        // Add product and verify
        $this->addToBasket($this->user, $this->product, 2);
        $this->assertBasketContains($this->user, $this->product, 2);

        // Check stock
        $this->assertProductStock($this->product, 10);

        // Remove product and verify
        $basket = $this->createBasketWithItems($this->user);
        $item = $basket->items->first();

        $this->actingAs($this->user)
            ->deleteJson("/api/basket/items/{$item->id}")
            ->assertStatus(204);

        $this->assertBasketIsEmpty($this->user);

        Log::info('Complete shopping flow test completed');
    }
}
