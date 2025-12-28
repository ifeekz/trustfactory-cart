export default function OrderCard({ order }) {
    return (
        <div className="border rounded p-4">
            <div className="flex justify-between items-center mb-3">
                <div>
                    <p className="font-medium">Order #{order.id}</p>
                    <p className="text-sm text-gray-500">
                        {new Date(order.created_at).toLocaleDateString()}
                    </p>
                </div>

                <p className="font-semibold">
                    ${(order.total / 100).toFixed(2)}
                </p>
            </div>

            <ul className="text-sm text-gray-700 space-y-1">
                {order.items.map((item) => (
                    <li key={item.id}>
                        {item.product.name} × {item.quantity}
                    </li>
                ))}
            </ul>
        </div>
    );
}
