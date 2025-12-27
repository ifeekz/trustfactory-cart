<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
    ];

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= config('shop.low_stock_threshold');
    }
}
