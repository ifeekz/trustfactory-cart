<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function show(Request $request)
    {
        if ($request->user()) {
            $cart = $this->cartService->getCart($request->user());

            return response()->json([
                'items' => $cart->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                    ],
                ]),
            ]);
        }

        $items = session('cart.items', []);

        $products = \App\Models\Product::whereIn(
            'id',
            array_keys($items)
        )->get()->keyBy('id');

        return response()->json([
            'items' => collect($items)->map(fn($qty, $productId) => [
                'product_id' => (int) $productId,
                'quantity' => $qty,
                'product' => $products[$productId] ?? null,
            ])->values(),
        ]);
    }




    public function store(AddToCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        if ($request->user()) {
            $this->cartService->addProduct(
                $request->user(),
                $product,
                $request->quantity
            );

            return response()->noContent();
        }

        // Guest cart (session-based)
        $cart = session()->get('cart.items', []);

        $cart[$product->id] = ($cart[$product->id] ?? 0) + $request->quantity;

        session()->put('cart.items', $cart);

        return response()->noContent();
    }


    public function update(UpdateCartItemRequest $request, Product $product)
    {
        if ($request->user()) {
            $this->cartService->updateProductQuantity(
                $request->user(),
                $product,
                $request->quantity
            );

            return response()->noContent();
        }

        $cart = session()->get('cart.items', []);

        if ($request->quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $request->quantity;
        }

        session()->put('cart.items', $cart);

        return response()->noContent();
    }


    public function destroy(Request $request, Product $product)
    {
        if ($request->user()) {
            $this->cartService->removeProduct(
                $request->user(),
                $product
            );

            return response()->noContent();
        }

        $cart = session()->get('cart.items', []);
        unset($cart[$product->id]);

        session()->put('cart.items', $cart);

        return response()->noContent();
    }


    public function checkout(Request $request)
    {
        $order = $this->cartService->checkout($request->user());

        return response()->json($order, 201);
    }
}
