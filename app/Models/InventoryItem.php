<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'item_id',
        'category',
        'stock',
        'unit',
        'unit_price',
        'status',
        'is_best_seller',
        'image_path',
    ];

    protected $casts = [
        'is_best_seller' => 'boolean',
        'stock' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'item_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
