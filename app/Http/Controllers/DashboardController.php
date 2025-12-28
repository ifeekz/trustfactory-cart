<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard', [
            'orders' => $orders,
        ]);
    }
}
