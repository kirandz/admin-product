<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockHistory;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        StockHistory::create([
            'product_id' => $product->id,
            'type' => 'initial',
            'quantity' => $product->stock,
            'reference_id' => null,
            'reference_type' => null,
            'before' => 0,
            'after' => $product->stock,
            'notes' => 'Initial stock',
        ]);
    }
}
