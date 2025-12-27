<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'user@trustfactory.test')->first();
        $products = Product::all();

        Order::factory()
            ->count(3)
            ->create(['user_id' => $user->id])
            ->each(function (Order $order) use ($products) {
                $items = $products->random(3);

                $total = 0;

                foreach ($items as $product) {
                    $quantity = rand(1, 3);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'quantity' => $quantity,
                    ]);

                    $total += $product->price * $quantity;
                }

                $order->update(['total' => $total]);
            });
    }
}
