<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        return [
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'supplier_id' => \App\Models\Supplier::inRandomOrder()->first()->id,
            'date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'total_amount' => 0,
            'status' => $this->faker->randomElement(['completed']),
            'notes' => $faker->sentence,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Purchase $purchase) {
            $purchaseItems = PurchaseItem::factory(3)->create(['purchase_id' => $purchase->id]);

            $totalAmount = 0;
            $purchaseItems->each(function ($purchaseItem) use ($purchase, &$totalAmount) {
                $product = Product::find($purchaseItem->product_id);
                $before = $product->stock;
                $product->stock += $purchaseItem->quantity;
                $after = $product->stock;
                $product->save();

                StockHistory::create([
                    'product_id' => $purchaseItem->product_id,
                    'type' => 'purchase',
                    'quantity' => $purchaseItem->quantity,
                    'reference_id' => $purchase->id,
                    'reference_type' => Purchase::class,
                    'before' => $before,
                    'after' => $after,
                    'notes' => 'Stock added from purchase',
                ]);

                $totalAmount += $purchaseItem->price * $purchaseItem->quantity;
            });

            $purchase->update([
                'total_amount' => $totalAmount,
            ]);
        });
    }
}
