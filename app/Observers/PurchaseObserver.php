<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\StockHistory;

class PurchaseObserver
{
    public function created(Purchase $purchase)
    {
        // Update stock for purchase items
        foreach ($purchase->purchaseItems as $item) {
            $product = Product::find($item->product_id);
            $before = $product->stock;
            $product->stock += $item->quantity;
            $after = $product->stock;
            $product->save();

            // Create stock history record
            StockHistory::create([
                'product_id' => $item->product_id,
                'type' => 'purchase',
                'quantity' => $item->quantity,
                'reference_id' => $purchase->id,
                'reference_type' => Purchase::class,
                'before' => $before,
                'after' => $after,
                'notes' => 'Stock added from purchase',
            ]);
        }
    }

    public function updated(Purchase $purchase)
    {
        // Handle purchase status changes
        if ($purchase->isDirty('status')) {
            $oldStatus = $purchase->getOriginal('status');
            $newStatus = $purchase->status;

            // If changing from 'completed' to 'cancelled', reverse stock addition
            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                foreach ($purchase->purchaseItems as $item) {
                    $product = Product::find($item->product_id);
                    $before = $product->stock;
                    $product->stock -= $item->quantity;
                    $after = $product->stock;
                    $product->save();

                    // Create stock history record
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'type' => 'purchase',
                        'quantity' => -$item->quantity,
                        'reference_id' => $purchase->id,
                        'reference_type' => Purchase::class,
                        'before' => $before,
                        'after' => $after,
                        'notes' => 'Stock removed due to purchase cancellation',
                    ]);
                }
            }

            // If changing from 'cancelled' to 'completed', add stock back
            if ($oldStatus === 'cancelled' && $newStatus === 'completed') {
                foreach ($purchase->purchaseItems as $item) {
                    $product = Product::find($item->product_id);
                    $before = $product->stock;
                    $product->stock += $item->quantity;
                    $after = $product->stock;
                    $product->save();

                    // Create stock history record
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'type' => 'purchase',
                        'quantity' => $item->quantity,
                        'reference_id' => $purchase->id,
                        'reference_type' => Purchase::class,
                        'before' => $before,
                        'after' => $after,
                        'notes' => 'Stock added from purchase',
                    ]);
                }
            }
        }
    }
}
