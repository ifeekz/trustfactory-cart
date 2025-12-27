import { useEffect, useState } from "react";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

import { fetchProducts } from "@/Services/productService";
import { addToCart } from "@/Services/cartService";

export default function Products({ auth }) {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLoading(true);

        fetchProducts(page, 12)
            .then((res) => {
                setProducts(res.data.data);
                setLastPage(res.data.last_page);
            })
            .finally(() => setLoading(false));
    }, [page]);

    const handleAdd = async (id) => {
        await addToCart(id, 1);
        window.dispatchEvent(new Event("cart-updated"));
    };

    return (
        <AuthenticatedLayout user={auth?.user}>
            <Head title="Products" />

            <div className="max-w-5xl mx-auto p-6">
                <h1 className="text-2xl font-semibold mb-4">Products</h1>

                {loading && <p>Loading products…</p>}

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
                                disabled={product.stock_quantity === 0}
                            >
                                Add to cart
                            </button>
                        </div>
                    ))}
                </div>

                {/* Pagination controls */}
                <div className="mt-6 flex items-center justify-center gap-4">
                    <button
                        onClick={() => setPage((p) => p - 1)}
                        disabled={page === 1}
                        className="px-4 py-2 border rounded disabled:opacity-50"
                    >
                        Previous
                    </button>

                    <span>
                        Page {page} of {lastPage}
                    </span>

                    <button
                        onClick={() => setPage((p) => p + 1)}
                        disabled={page === lastPage}
                        className="px-4 py-2 border rounded disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
