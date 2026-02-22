<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'transaction_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_number',
        'supplier',
        'reason',
        'notes',
        'transaction_date',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate reference number like SI-2026-0001 or SO-2026-0001
     */
    public static function generateReference(string $type): string
    {
        $prefix = $type === 'stock_in' ? 'SI' : 'SO';
        $year = date('Y');
        $last = static::where('reference_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($last && $last->reference_number) {
            $parts = explode('-', $last->reference_number);
            $nextNum = intval(end($parts)) + 1;
        }

        return "{$prefix}-{$year}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
