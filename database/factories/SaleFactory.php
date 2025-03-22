<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        return [
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'customer_id' => \App\Models\Customer::inRandomOrder()->first()->id,
            'date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total_amount' => 0,
            'status' => $this->faker->randomElement(['completed']),
            'notes' => $this->faker->sentence,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Sale $sale) {
            $saleItems = SaleItem::factory(3)->create(['sale_id' => $sale->id]);

            $subtotal = 0;

            $saleItems->each(function ($saleItem) use ($sale, &$subtotal) {
                $product = Product::find($saleItem->product_id);
                $before = $product->stock;
                $product->stock -= $saleItem->quantity;
                $after = $product->stock;
                $product->save();

                StockHistory::create([
                    'product_id' => $saleItem->product_id,
                    'type' => 'sale',
                    'quantity' => $saleItem->quantity,
                    'reference_id' => $sale->id,
                    'reference_type' => Sale::class,
                    'before' => $before,
                    'after' => $after,
                    'notes' => 'Stock reduced from sale',
                ]);

                $subtotal += $saleItem->price * $saleItem->quantity;
            });

            $tax = $subtotal * 0.10;
            $discount = $subtotal * 0.05;
            $totalAmount = $subtotal + $tax - $discount;

            $sale->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
            ]);
        });
    }

    public function forToday()
    {
        return $this->state(function (array $attributes) {
            return [
                'date' => now()->format('Y-m-d'),
            ];
        });
    }
}
