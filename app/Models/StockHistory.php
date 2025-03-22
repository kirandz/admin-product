<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'type', // 'purchase', 'sale', 'adjustment'
        'reference_id', // ID of purchase, sale, or adjustment
        'reference_type', // Model class name
        'before', // Stock before operation
        'after', // Stock after operation
        'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
