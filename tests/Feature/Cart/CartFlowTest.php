<?php

namespace Tests\Feature\Cart;

use App\Jobs\LowStockNotificationJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CartFlowTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_view_empty_cart(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->getJson('/api/cart');

        $response->assertOk()
            ->assertJson([
                'user_id' => $this->user->id,
                'items' => [],
            ]);
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($this->user)
            ->patchJson("/api/cart/items/{$product->id}", [
                'quantity' => 5,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/cart/items/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_user_cannot_add_more_than_available_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->assertStatus(422);
    }

    public function test_user_can_checkout_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/cart/checkout');

        $response->assertCreated()
            ->assertJson([
                'user_id' => $this->user->id,
                'total' => 2000,
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total' => 2000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_checkout_clears_cart_and_reduces_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/checkout');

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);

        $this->assertEquals(3, $product->fresh()->stock_quantity);
    }

    public function test_low_stock_notification_is_dispatched_on_checkout(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'stock_quantity' => config('shop.low_stock_threshold') + 1,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        Bus::assertDispatched(LowStockNotificationJob::class);
    }
}
