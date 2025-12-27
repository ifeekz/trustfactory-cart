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
        return $this->cartService->getCart($request->user());
    }

    public function store(AddToCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        $this->cartService->addProduct(
            $request->user(),
            $product,
            $request->quantity
        );

        return response()->noContent();
    }

    public function update(
        UpdateCartItemRequest $request,
        Product $product
    ) {
        $this->cartService->updateProductQuantity(
            $request->user(),
            $product,
            $request->quantity
        );

        return response()->noContent();
    }

    public function destroy(Request $request, Product $product)
    {
        $this->cartService->removeProduct(
            $request->user(),
            $product
        );

        return response()->noContent();
    }

    public function checkout(Request $request)
    {
        $order = $this->cartService->checkout($request->user());

        return response()->json($order, 201);
    }
}
