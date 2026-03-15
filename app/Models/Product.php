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
        'unit',
        'unit_price',
        'reorder_point',
        'status',
        'is_best_seller',
        'image_path',
    ];

    protected $casts = [
        'is_best_seller' => 'boolean',
        'stock'          => 'integer',
        'unit_price'     => 'decimal:2',
        'reorder_point'  => 'integer',
    ];

    public function phaseItems()
    {
        return $this->hasMany(OrderPhaseItem::class, 'product_id', 'Product_Id');
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

    /**
     * Recalculate and persist the product status based on stock vs reorder_point.
     */
    public function updateStockStatus(): void
    {
        if ($this->stock <= 0) {
            $status = 'Out of Stock';
        } elseif ($this->stock < $this->reorder_point) {
            $status = 'Low Stock';
        } else {
            $status = 'In Stock';
        }

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }

    /**
     * Send low_stock notification to admins if stock just crossed below reorder_point.
     */
    public function checkAndNotifyLowStock(int $previousStock): void
    {
        if ($previousStock >= $this->reorder_point && $this->stock < $this->reorder_point && $this->stock > 0) {
            $adminIds = User::where('role', 'admin')
                ->orWhere('role', 'super_admin')
                ->pluck('User_Id')
                ->toArray();

            Notification::sendToMany(
                $adminIds,
                'low_stock',
                'Low Stock Alert',
                "{$this->name} ({$this->item_code}) has dropped to {$this->stock} {$this->unit}. Reorder point: {$this->reorder_point}.",
                ['product_id' => $this->Product_Id, 'stock' => $this->stock, 'reorder_point' => $this->reorder_point]
            );
        }
    }
}
