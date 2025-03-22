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

        User::factory(5)->create();
        Customer::factory(5)->create();
        Supplier::factory(5)->create();
        Product::factory(20)->create();
        Purchase::factory(20)->create();
        Sale::factory(15)->create();
        Sale::factory(5)->forToday()->create();
    }
}
