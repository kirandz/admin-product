<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        $products = [
            'Laptop' => 'Computers',
            'Smartphone' => 'Mobile Devices',
            'Tablet' => 'Mobile Devices',
            'Headphones' => 'Accessories',
            'Smartwatch' => 'Accessories',
            'Camera' => 'Electronics',
            'Printer' => 'Office Equipment',
            'Monitor' => 'Computers',
            'Keyboard' => 'Accessories',
            'Mouse' => 'Accessories'
        ];

        $productName = $faker->randomElement(array_keys($products));
        $category = $products[$productName];
        $initialStock = $faker->numberBetween(10, 100);

        return [
            'name' => $productName,
            'code' => $this->faker->unique()->numerify('PRD-#####'),
            'category' => $category,
            'description' => $faker->sentence,
            'purchase_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 20, 200),
            'stock' => $initialStock,
        ];
    }
}
