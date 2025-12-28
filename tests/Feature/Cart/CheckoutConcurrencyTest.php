<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_user_can_checkout_last_item()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 1,
        ]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cartService = app(CartService::class);

        // Both users add the same product
        $cartService->addProduct($userA, $product, 1);
        $cartService->addProduct($userB, $product, 1);

        /**
         * Simulate concurrent checkout by manually locking the product row
         * in one transaction while attempting checkout in another.
         */

        DB::beginTransaction();

        // Lock the product row (simulates User A entering checkout first)
        DB::table('products')
            ->where('id', $product->id)
            ->lockForUpdate()
            ->first();

        // User B attempts checkout while product is locked
        try {
            $cartService->checkout($userB);
            $this->fail('Expected checkout to fail due to insufficient stock.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stock', $e->errors());
        }

        // Complete User A checkout
        $orderA = $cartService->checkout($userA);

        DB::commit();

        // Assertions
        $this->assertNotNull($orderA);

        $this->assertDatabaseCount('orders', 1);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 0,
        ]);
    }
}
