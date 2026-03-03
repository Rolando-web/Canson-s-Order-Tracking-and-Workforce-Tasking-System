<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $table = 'stock_out';
    protected $primaryKey = 'Stock_Out_Id';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'reference_number',
        'reason',
        'order_id',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'Item_Id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'Order_Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_Id');
    }
}