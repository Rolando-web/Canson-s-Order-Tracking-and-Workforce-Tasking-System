<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $table = 'stock_in';
    protected $primaryKey = 'Stock_In_Id';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'quantity',
        'previous_stock',
        'new_stock',
        'unit_cost',
        'reference_number',
        'supplier_id',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'unit_cost'  => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'Product_Id');
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