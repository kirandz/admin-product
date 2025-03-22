<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        User::factory(2)->create();
        Customer::factory(3)->create();
        Supplier::factory(3)->create();
        Product::factory(10)->create();
        Purchase::factory(5)->create();
        Sale::factory(3)->create();
        Sale::factory(2)->forToday()->create();
    }
}
