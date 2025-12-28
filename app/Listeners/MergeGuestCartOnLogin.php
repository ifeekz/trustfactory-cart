<?php

namespace App\Listeners;

use App\Models\Product;
use App\Services\CartService;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MergeGuestCartOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private CartService $cartService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $session = session();
        $guestItems = $session->get('cart.items', []);

        if (empty($guestItems)) {
            return;
        }

        foreach ($guestItems as $productId => $quantity) {
            $product = Product::find($productId);

            if (! $product) {
                continue;
            }

            $this->cartService->addProduct(
                $event->user,
                $product,
                $quantity
            );
        }

        $session->forget('cart.items');
    }
}
