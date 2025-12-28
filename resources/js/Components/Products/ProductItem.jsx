export default function ProductItem({ product, onAdd }) {
    const isOutOfStock = product.stock_quantity === 0;

    return (
        <div className="border rounded p-4 flex flex-col justify-between">
            <div>
                <h2 className="font-medium">{product.name}</h2>

                <p className="mt-1 text-sm text-gray-700">
                    ${(product.price / 100).toFixed(2)}
                </p>

                <p className="mt-1 text-sm text-gray-500">
                    Stock: {product.stock_quantity}
                </p>
            </div>

            <button
                onClick={() => onAdd(product.id)}
                disabled={isOutOfStock}
                className={[
                    "mt-4 px-3 py-1 rounded text-white",
                    isOutOfStock
                        ? "bg-gray-400 cursor-not-allowed"
                        : "bg-black hover:bg-gray-800",
                ].join(" ")}
            >
                Add to cart
            </button>
        </div>
    );
}
