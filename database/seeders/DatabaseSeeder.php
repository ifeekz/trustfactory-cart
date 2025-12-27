<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => config('shop.admin_email'),
        ]);

        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'user@trustfactory.test',
        ]);

        Product::factory()
            ->count(12)
            ->create();

        $this->call(OrderSeeder::class);
    }
}
