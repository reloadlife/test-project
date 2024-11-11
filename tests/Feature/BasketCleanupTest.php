<?php

namespace Tests\Feature;

use App\Models\{Basket, User, Product};
use App\Console\Commands\DailyClearUserBasket;
use App\Events\BasketClearIncoming;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Log, Notification, Event};
use Tests\TestCase;

class BasketCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Log::info(message: 'Starting basket cleanup test setup');
        Notification::fake();
        Event::fake();

        $auth = $this->createAuthenticatedUser([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $this->user = $auth['user'];
        $this->product = $this->createProduct(10, 1000);
        Log::info('Test setup completed', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_old_baskets_are_cleared_after_24_hours(): void
    {
        Log::info('Testing basket cleanup after 24 hours');
        $addResponse = $this->addToBasket($this->user, $this->product, 1);
        $addResponse->assertSuccessful();

        Log::info("timetravel");
        $this->travel(25)->hours();

        $this->artisan('app:daily-clear-user-basket')->assertSuccessful();

        $this->assertBasketIsEmpty($this->user);
        Log::info('Basket cleanup test completed successfully');
    }

    public function test_users_are_notified_before_basket_expiration(): void
    {
        Log::info('Testing basket expiration notification');
        $addResponse = $this->addToBasket($this->user, $this->product, 1);
        $addResponse->assertSuccessful();

        Log::info("timetravel");
        $this->travel(value: 23)->hours();

        $this->artisan('app:daily-clear-user-basket --notify')->assertSuccessful();

        Event::assertDispatched(BasketClearIncoming::class);
        Log::info('Basket notification test completed successfully');
    }

    public function test_basket_cleanup_with_empty_baskets(): void
    {
        Log::info('Testing cleanup with empty baskets');
        $emptyBasket = $this->createBasketWithItems($this->user);

        Log::info("timetravel");
        $this->travel(value: 25)->hours();

        $filledBasket = $this->createBasketWithItems($this->user);

        Log::info('Created test baskets', [
            'empty_basket_id' => $emptyBasket->id,
            'filled_basket_id' => $filledBasket->id
        ]);

        $this->artisan('app:daily-clear-user-basket')->assertSuccessful();

        $this->assertDatabaseMissing('baskets', ['id' => $emptyBasket->id]);
        $this->assertDatabaseHas('baskets', ['id' => $filledBasket->id]);
        Log::info('Empty baskets cleanup test completed successfully');
    }
}
