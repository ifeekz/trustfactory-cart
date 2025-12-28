export default function CartSummary({ items, loading, onCheckout }) {
    const subtotal = items.reduce(
        (sum, item) => sum + item.product.price * item.quantity,
        0
    );

    return (
        <div className="mt-8 border-t pt-6">
            <div className="flex justify-between items-center mb-4">
                <span className="text-lg font-medium">Subtotal</span>
                <span className="text-lg font-semibold">
                    ${(subtotal / 100).toFixed(2)}
                </span>
            </div>

            <button
                disabled={loading}
                onClick={onCheckout}
                className="w-full bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50"
            >
                Checkout
            </button>
        </div>
    );
}
