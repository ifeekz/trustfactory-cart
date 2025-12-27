import { useEffect, useState } from "react";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

import { fetchProducts } from "@/Services/productService";
import { addToCart } from "@/Services/cartService";

export default function Products({ auth }) {
    const [products, setProducts] = useState([]);

    useEffect(() => {
        fetchProducts().then((res) => setProducts(res.data));
    }, []);

    const handleAdd = async (id) => {
        await addToCart(id, 1);
        alert("Added to cart");
    };

    return (
        <AuthenticatedLayout user={auth?.user}>
            <Head title="Products" />
            <div className="max-w-5xl mx-auto p-6">
                <h1 className="text-2xl font-semibold mb-4">Products</h1>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {products.map((product) => (
                        <div key={product.id} className="border rounded p-4">
                            <h2 className="font-medium">{product.name}</h2>
                            <p>${(product.price / 100).toFixed(2)}</p>
                            <p className="text-sm text-gray-500">
                                Stock: {product.stock_quantity}
                            </p>

                            <button
                                onClick={() => handleAdd(product.id)}
                                className="mt-2 bg-black text-white px-3 py-1 rounded"
                            >
                                Add to cart
                            </button>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
