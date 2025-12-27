<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Jobs\LowStockNotificationJob;

use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;

use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $cartService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = app(CartService::class);
        $this->user = User::factory()->create();
    }


    public function test_it_adds_a_product_to_the_cart(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);

        $cart = $this->cartService->getCart($this->user);

        $this->assertCount(1, $cart->items);
        $this->assertEquals(2, $cart->items->first()->quantity);
    }

    public function test_it_increments_quantity_for_existing_product(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);
        $this->cartService->addProduct($this->user, $product, 3);

        $cart = $this->cartService->getCart($this->user);

        $this->assertEquals(5, $cart->items->first()->quantity);
    }

    public function test_it_throws_exception_when_stock_is_insufficient(): void
    {
        $this->expectException(ValidationException::class);

        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);

        $this->cartService->addProduct($this->user, $product, 5);
    }

    public function test_it_updates_product_quantity(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);
        $this->cartService->updateProductQuantity($this->user, $product, 5);

        $cart = $this->cartService->getCart($this->user);

        $this->assertEquals(5, $cart->items->first()->quantity);
    }

    public function test_quantity_zero_removes_product_from_cart(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);
        $this->cartService->updateProductQuantity($this->user, $product, 0);

        $cart = $this->cartService->getCart($this->user);

        $this->assertCount(0, $cart->items);
    }

    public function test_checkout_creates_order_and_clears_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'stock_quantity' => 10,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);

        $order = $this->cartService->checkout($this->user);

        $this->assertEquals(2000, $order->total);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cart = $this->cartService->getCart($this->user);
        $this->assertCount(0, $cart->items);
    }

    public function test_checkout_decrements_product_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);
        $this->cartService->checkout($this->user);

        $this->assertEquals(3, $product->fresh()->stock_quantity);
    }

    public function test_low_stock_notification_job_is_dispatched(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'stock_quantity' => config('shop.low_stock_threshold') + 1,
        ]);

        $this->cartService->addProduct($this->user, $product, 1);

        Bus::assertDispatched(LowStockNotificationJob::class);
    }
}
