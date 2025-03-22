<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockHistory;

class SaleObserver
{
    public function created(Sale $sale)
    {
        // Update stock for sale items
        foreach ($sale->saleItems as $item) {
            $product = Product::find($item->product_id);
            $before = $product->stock;
            $product->stock -= $item->quantity;
            $after = $product->stock;
            $product->save();

            // Create stock history record
            StockHistory::create([
                'product_id' => $item->product_id,
                'type' => 'sale',
                'quantity' => $item->quantity,
                'reference_id' => $sale->id,
                'reference_type' => Sale::class,
                'before' => $before,
                'after' => $after,
                'notes' => 'Stock reduced from sale',
            ]);
        }
    }

    public function updated(Sale $sale)
    {
        // Handle sale status changes
        if ($sale->isDirty('status')) {
            $oldStatus = $sale->getOriginal('status');
            $newStatus = $sale->status;

            // If changing from 'completed' to 'cancelled', reverse stock reduction
            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                foreach ($sale->saleItems as $item) {
                    $product = Product::find($item->product_id);
                    $before = $product->stock;
                    $product->stock += $item->quantity;
                    $after = $product->stock;
                    $product->save();

                    // Create stock history record
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'type' => 'sale',
                        'quantity' => -$item->quantity,
                        'reference_id' => $sale->id,
                        'reference_type' => Sale::class,
                        'before' => $before,
                        'after' => $after,
                        'notes' => 'Stock added back due to sale cancellation',
                    ]);
                }
            }

            // If changing from 'cancelled' to 'completed', reduce stock again
            if ($oldStatus === 'cancelled' && $newStatus === 'completed') {
                foreach ($sale->saleItems as $item) {
                    $product = Product::find($item->product_id);
                    $before = $product->stock;
                    $product->stock -= $item->quantity;
                    $after = $product->stock;
                    $product->save();

                    // Create stock history record
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'type' => 'sale',
                        'quantity' => $item->quantity,
                        'reference_id' => $sale->id,
                        'reference_type' => Sale::class,
                        'before' => $before,
                        'after' => $after,
                        'notes' => 'Stock reduced from sale',
                    ]);
                }
            }
        }
    }
}
