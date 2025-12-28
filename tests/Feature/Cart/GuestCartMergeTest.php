<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestCartMergeTest extends TestCase
{

    #[Test]
    public function guest_cart_is_merged_into_user_cart_on_login()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        // Guest adds product to cart (session-based)
        $this->post('/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertNoContent();

        // Ensure guest session has cart data
        $this->assertEquals(
            2,
            session('cart.items')[$product->id]
        );

        $user = User::factory()->create();

        // Act: guest logs in
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        // Assert: session cart is cleared
        $this->assertNull(session('cart.items'));

        // Assert: cart exists for user with merged item
        $this->actingAs($user);

        $response = $this->get('/cart')
            ->assertOk()
            ->json();

        $this->assertCount(1, $response['items']);
        $this->assertEquals(
            $product->id,
            $response['items'][0]['product_id']
        );
        $this->assertEquals(
            2,
            $response['items'][0]['quantity']
        );
    }
}
