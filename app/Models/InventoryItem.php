<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'Item_Id';

    protected $fillable = [
        'name',
        'item_code',
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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'inventory_item_id', 'Item_Id');
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'item_id', 'Item_Id');
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class, 'item_id', 'Item_Id');
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class, 'item_id', 'Item_Id');
    }
}