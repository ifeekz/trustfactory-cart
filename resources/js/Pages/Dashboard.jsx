import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

import OrderCard from '@/Components/Orders/OrderCard';

export default function Dashboard({ orders }) {
    const { data, current_page, last_page } = orders;
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    My Recent Orders
                </h2>
            }
        >
            <Head title="Recent orders" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {data.length === 0 && (
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6 text-gray-600">
                                You haven't placed any orders yet.
                            </div>
                        </div>
                    )}

                    <div className="space-y-6">
                        {data.map((order) => (
                            <OrderCard key={order.id} order={order} />
                        ))}
                    </div>

                    <div className="mt-8 flex items-center justify-center gap-4">
                        <button
                            disabled={current_page === 1}
                            onClick={() =>
                                router.get("/dashboard", {
                                    page: current_page - 1,
                                })
                            }
                            className="px-4 py-2 border rounded disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <span className="text-sm text-gray-600">
                            Page {current_page} of {last_page}
                        </span>

                        <button
                            disabled={current_page === last_page}
                            onClick={() =>
                                router.get("/dashboard", {
                                    page: current_page + 1,
                                })
                            }
                            className="px-4 py-2 border rounded disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
