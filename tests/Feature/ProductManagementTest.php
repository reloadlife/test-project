<?php

namespace Tests\Feature;

use App\Events\ProductCreated;
use App\Models\{User};
use App\Notifications\NewProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\{Log, Notification, Event};
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private array $validProductData =
    [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 1000,
        'stock' => 10
    ];
    private AnonymousNotifiable $adminNotifiable;

    protected function setUp(): void
    {
        parent::setUp();
        Log::info('Starting product management test setup');

        Notification::fake(); // not to run the actual mail send functions
        Event::fake(); // same reason
        $this->admin = $this->createAdmin();
        Log::info('Test setup completed', [
            'admin_id' => $this->admin->id
        ]);

        $this->adminNotifiable = new AnonymousNotifiable();
        $this->adminNotifiable->route("mail", "me@mamad.dev");
    }

    public function test_product_creation_requires_authentication(): void
    {
        Log::info('Testing product creation authentication');

        $response = $this->postJson('/api/products', $this->validProductData);
        $response->assertStatus(401);

        Log::info('Product creation authentication test completed');
    }

    public function test_admin_can_create_product(): void
    {
        Log::info('Testing valid product creation');

        $product = $this->createProduct();
        $this->assertProductExists([
            'id' => $product->id,
            'name' => $product->name
        ]);
        Event::assertDispatched(ProductCreated::class);
        Log::info('Product creation test completed');
    }

    public function test_admin_can_update_product(): void
    {
        Log::info('Testing product update');
        $product = $this->createProduct();
        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 2000
        ];
        $response = $this->actingAs($this->admin)
            ->putJson("/api/products/{$product->id}", $updateData);
        $response->assertStatus(200)
            ->assertJson($updateData);
        Log::info('Product update test completed');
    }

    public function test_admin_can_delete_product(): void
    {
        Log::info('Testing product deletion');
        $product = $this->createProduct();
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Log::info('Product deletion test completed');
    }


    public static function invalidProductDataProvider(): array
    {
        return [
            'negative price' => ['price', -1000, 'Price must be at least 0'],
            'negative stock' => ['stock', -10, 'Stock must be at least 0'],
            'empty name' => ['name', '', 'The name field is required'],
            'empty description' => ['description', '', 'The description field is required'],
            'name too long' => ['name', str_repeat('a', 256), 'The name must not exceed 255 characters'],
            'non-integer price' => ['price', 10.5, 'The price must be an integer'],
            'non-integer stock' => ['stock', 5.5, 'The stock must be an integer'],
        ];
    }

    #[DataProvider('invalidProductDataProvider')]
    public function test_cannot_create_product_with_invalid_data(string $field, $value, string $errorMessage): void
    {
        Log::info('Testing invalid product data', [
            'field' => $field,
            'value' => $value,
            'expected_error' => $errorMessage
        ]);

        $invalidData = array_merge(
            $this->validProductData,
            [$field => $value]
        );

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);

        Log::info('Invalid product data test completed', [
            'field' => $field,
            'response' => $response->json()
        ]);
    }

    public function test_can_list_all_products(): void
    {
        Log::info('Testing product listing');
        $products = collect(range(1, 3))->map(fn() => $this->createProduct());
        $response = $this->actingAs($this->admin)
            ->getJson('/api/products');
        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'created_at',
                    'updated_at'
                ]
            ]);
        Log::info('Product listing test completed');
    }

    public function test_can_view_single_product(): void
    {
        Log::info('Testing single product view');
        $product = $this->createProduct();
        $response = $this->actingAs($this->admin)
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name
            ]);

        Log::info('Single product view test completed');
    }
}
