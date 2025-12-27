<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(
            max((int) $request->query('limit', 12), 1),
            50
        );

        return Product::query()
            ->select('id', 'name', 'price', 'stock_quantity')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
