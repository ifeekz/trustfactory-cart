import { useEffect, useState } from "react";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { router } from "@inertiajs/react";

import {
    fetchCart,
    updateCartItem,
    removeCartItem,
    checkout,
} from "@/Services/cartService";

export default function Cart({ auth }) {
    const [cart, setCart] = useState(null);
    const [loading, setLoading] = useState(false);

    const loadCart = async () => {
        const res = await fetchCart();
        setCart(res.data);
    };

    const refreshCart = async () => {
        await loadCart();
        window.dispatchEvent(new Event("cart-updated"));
    };

    useEffect(() => {
        loadCart();
    }, []);

    if (!cart) {
        return (
            <AuthenticatedLayout user={auth?.user}>
                <div className="max-w-4xl mx-auto p-6">
                    <p>Loading cart…</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    const handleQuantityChange = async (productId, quantity) => {
        setLoading(true);

        try {
            await updateCartItem(productId, quantity);
            await refreshCart();
        } catch (error) {
            if (error.response?.status === 422) {
                const message =
                    error.response.data?.errors?.quantity?.[0] ||
                    error.response.data?.message ||
                    "Invalid quantity.";

                alert(message);

                await loadCart();
            } else {
                alert("Something went wrong. Please try again.");
            }
        } finally {
            setLoading(false);
        }
    };


    const handleRemove = async (productId) => {
        setLoading(true);

        await removeCartItem(productId);
        await refreshCart();

        setLoading(false);
    };

    const handleCheckout = async () => {
        if (!auth?.user) {
            router.get("/login", { redirect: "/cart" });
            return;
        }

        setLoading(true);

        await checkout();

        await refreshCart();

        setLoading(false);
        // alert("Order placed successfully");
    };


    return (
        <AuthenticatedLayout user={auth?.user}>
            <Head title="Cart" />

            <div className="max-w-4xl mx-auto p-6">
                <h1 className="text-2xl font-semibold mb-4">Your Cart</h1>

                {cart.items.length === 0 && <p>Your cart is empty.</p>}

                {cart.items.map((item) => (
                    <div
                        key={item.product_id}
                        className="flex justify-between items-center mb-4"
                    >
                        <div>
                            <p className="font-medium">{item.product.name}</p>
                            <p className="text-sm text-gray-500">
                                ${(item.product.price / 100).toFixed(2)}
                            </p>
                        </div>

                        <input
                            type="number"
                            min="0"
                            value={item.quantity}
                            disabled={loading}
                            onChange={(e) =>
                                handleQuantityChange(
                                    item.product_id,
                                    Number(e.target.value)
                                )
                            }
                            className="w-16 border rounded px-2 py-1"
                        />

                        <button
                            disabled={loading}
                            onClick={() => handleRemove(item.product_id)}
                            className="text-red-600"
                        >
                            Remove
                        </button>
                    </div>
                ))}

                {cart.items.length > 0 && (
                    <button
                        disabled={loading}
                        onClick={handleCheckout}
                        className="mt-6 bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50"
                    >
                        Checkout
                    </button>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
