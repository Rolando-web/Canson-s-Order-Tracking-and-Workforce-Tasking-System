<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'Product_Id';

    protected $fillable = [
        'name',
        'item_code',
        'category',
        'stock',
        'unit_price',
        'status',
        'is_best_seller',
        'image_path',
    ];

    protected $casts = [
        'is_best_seller' => 'boolean',
        'stock'          => 'integer',
        'unit_price'     => 'decimal:2',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'Product_Id');
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'product_id', 'Product_Id');
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class, 'product_id', 'Product_Id');
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class, 'product_id', 'Product_Id');
    }

    public function phaseItems()
    {
        return $this->hasMany(OrderPhaseItem::class, 'product_id', 'Product_Id');
    }
}
