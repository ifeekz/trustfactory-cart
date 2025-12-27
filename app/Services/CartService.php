<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Jobs\LowStockNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getCart(User $user): Cart
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        return $cart->load('items.product');
        // return Cart::with('items.product')
        //     ->firstOrCreate(['user_id' => $user->id]);
    }

    public function addProduct(User $user, Product $product, int $quantity): void
    {
        $cart = $this->getCart($user);

        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
        ]);

        $newQuantity = $item->quantity + $quantity;

        $this->ensureStockAvailable($product, $newQuantity);

        $item->quantity = $newQuantity;
        $item->save();

        $this->notifyIfLowStock($product, $newQuantity);
    }

    public function updateProductQuantity(User $user, Product $product, int $quantity): void
    {
        $cart = $this->getCart($user);

        if ($quantity <= 0) {
            $this->removeProduct($user, $product);
            return;
        }

        $this->ensureStockAvailable($product, $quantity);

        $cart->items()
            ->where('product_id', $product->id)
            ->update(['quantity' => $quantity]);

        $this->notifyIfLowStock($product, $quantity);
    }

    public function removeProduct(User $user, Product $product): void
    {
        $this->getCart($user)
            ->items()
            ->where('product_id', $product->id)
            ->delete();
    }

    public function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            $cart = $this->getCart($user);

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart is empty.',
                ]);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $cart->total(),
            ]);

            foreach ($cart->items as $item) {
                $this->ensureStockAvailable($item->product, $item->quantity);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);

                $item->product->decrement('stock_quantity', $item->quantity);

                $this->notifyIfLowStock(
                    $item->product,
                    $item->product->stock_quantity
                );
            }

            $cart->items()->delete();

            return $order;
        });
    }

    private function ensureStockAvailable(Product $product, int $quantity): void
    {
        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'stock' => "{$product->name} does not have enough stock.",
            ]);
        }
    }

    private function notifyIfLowStock(Product $product, int $remainingQuantity): void
    {
        if ($remainingQuantity <= config('shop.low_stock_threshold')) {
            LowStockNotificationJob::dispatch($product);
        }
    }
}
