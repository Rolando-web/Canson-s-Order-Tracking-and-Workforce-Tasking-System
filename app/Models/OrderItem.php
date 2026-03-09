<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'Order_Item_Id';

    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'quantity',
        'completed_qty',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'completed_qty'=> 'integer',
        'unit_price'   => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'Order_Id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'product_id', 'Product_Id');
    }
}