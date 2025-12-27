import { useEffect, useState } from "react";
import {
    fetchCart,
    updateCartItem,
    removeCartItem,
    checkout,
} from "@/Services/cartService";

export default function Cart() {
    const [cart, setCart] = useState(null);

    const loadCart = async () => {
        const res = await fetchCart();
        setCart(res.data);
    };

    useEffect(() => {
        loadCart();
    }, []);

    if (!cart) return null;

    const handleCheckout = async () => {
        await checkout();
        alert("Order placed");
        loadCart();
    };

    return (
        <div className="max-w-4xl mx-auto p-6">
            <h1 className="text-2xl font-semibold mb-4">Your Cart</h1>

            {cart.items.length === 0 && <p>Your cart is empty.</p>}

            {cart.items.map((item) => (
                <div
                    key={item.id}
                    className="flex justify-between items-center mb-3"
                >
                    <div>
                        <p>{item.product.name}</p>
                        <p className="text-sm text-gray-500">
                            ${(item.product.price / 100).toFixed(2)}
                        </p>
                    </div>

                    <input
                        type="number"
                        min="0"
                        value={item.quantity}
                        onChange={(e) =>
                            updateCartItem(
                                item.product.id,
                                Number(e.target.value)
                            ).then(loadCart)
                        }
                        className="w-16 border rounded px-2"
                    />

                    <button
                        onClick={() =>
                            removeCartItem(item.product.id).then(loadCart)
                        }
                        className="text-red-600"
                    >
                        Remove
                    </button>
                </div>
            ))}

            {cart.items.length > 0 && (
                <button
                    onClick={handleCheckout}
                    className="mt-4 bg-green-600 text-white px-4 py-2 rounded"
                >
                    Checkout
                </button>
            )}
        </div>
    );
}
