<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $table = 'stock_in';
    protected $primaryKey = 'Stock_In_Id';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'quantity',
        'reference_number',
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'Supplier_Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_Id');
    }
}