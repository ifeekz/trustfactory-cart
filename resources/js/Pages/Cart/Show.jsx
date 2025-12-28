import { useEffect, useState } from "react";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { router } from "@inertiajs/react";

import CartItem from "@/Components/Cart/CartItem";
import CartSummary from "@/Components/Cart/CartSummary";

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

    if (!cart) {
        return (
            <AuthenticatedLayout user={auth?.user}>
                <div className="max-w-4xl mx-auto p-6">
                    <p>Loading cart…</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth?.user}>
            <Head title="Cart" />

            <div className="max-w-4xl mx-auto p-6">
                <h1 className="text-2xl font-semibold mb-4">Your Cart</h1>

                {cart.items.length === 0 && <p>Your cart is empty.</p>}

                {cart.items.map((item) => (
                    <CartItem
                        key={item.product_id}
                        item={item}
                        loading={loading}
                        onQuantityChange={handleQuantityChange}
                        onRemove={handleRemove}
                    />
                ))}

                {cart.items.length > 0 && (
                    <CartSummary
                        items={cart.items}
                        loading={loading}
                        onCheckout={handleCheckout}
                    />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
