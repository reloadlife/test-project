<?php

namespace Tests\Feature;

use App\Models\{User, Product};
use App\Notifications\NewProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Log, Notification};
use Tests\TestCase;

class BasketManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private Product $outofStockProdct;

    protected function setUp(): void
    {
        parent::setUp();
        Log::info('Starting basket management test setup');
        $auth = $this->createAuthenticatedUser([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->user = $auth['user'];
        $this->product = $this->createProduct(10, 1000);
        $this->outofStockProdct = $this->createProduct(0, 10000);

        Log::info('Test setup completed', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_user_can_add_product_to_basket(): void
    {
        Log::info('Testing add product to basket');

        $response = $this->addToBasket($this->user, $this->product);
        $response->assertStatus(201);

        $this->assertBasketContains($this->user, $this->product);
        Log::info('Add to basket test completed');
    }

    public function test_user_cannot_add_duplicate_product(): void
    {
        Log::info('Testing duplicate product prevention');

        $this->addToBasket($this->user, $this->product);
        $this->assertBasketContains($this->user, $this->product);

        $response = $this->addToBasket($this->user, $this->product, 5);
        $response->assertStatus(201)
            ->assertJson([
                'product_id' => $this->product->id,
                'quantity' => 6
            ]);

        Log::info('Duplicate product test completed');
    }

    public function test_user_cannot_add_product_exceeding_stock(): void
    {
        Log::info('Testing stock limit validation');

        $response = $this->addToBasket($this->user, $this->product, quantity: 20);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Requested quantity is not available']);

        Log::info('Stock limit test completed');
    }

    public function test_user_cannot_add_product_with_nothing_in_stock(): void
    {
        Log::info('Testing nothing in stock limit validation');

        $response = $this->addToBasket($this->user, $this->outofStockProdct, quantity: 1);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Requested quantity is not available']);

        Log::info('Stock nothing in stock limit test completed');
    }

    public function test_user_can_remove_basket_item(): void
    {
        Log::info('Testing basket item removal');

        $addResponse = $this->addToBasket($this->user, $this->product);
        $itemId = $addResponse->json('id');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/basket/items/{$itemId}");

        $response->assertStatus(204);
        $this->assertBasketIsEmpty($this->user);
        Log::info('Basket item removal test completed');
    }

    public function test_basket_persists_within_24_hours(): void
    {
        Log::info('Testing basket persistence');
        $this->addToBasket($this->user, $this->product);
        $this->travel(23)->hours();
        $this->assertBasketContains($this->user, $this->product);
        Log::info('Basket persistence test completed');
    }

    public function test_basket_clears_after_24_hours(): void
    {
        Log::info('Testing basket expiration');
        $basket = $this->createExpiredBasket($this->user);
        $this->artisan('app:daily-clear-user-basket');
        $this->assertBasketIsEmpty($this->user);
        Log::info('Basket expiration test completed');
    }
}
