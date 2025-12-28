export default function CartItem({
    item,
    loading,
    onQuantityChange,
    onRemove,
}) {
    return (
        <div className="flex justify-between items-center mb-4">
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
                    onQuantityChange(item.product_id, Number(e.target.value))
                }
                className="w-16 border rounded px-2 py-1"
            />

            <button
                disabled={loading}
                onClick={() => onRemove(item.product_id)}
                className="text-red-600 disabled:opacity-50"
            >
                Remove
            </button>
        </div>
    );
}
