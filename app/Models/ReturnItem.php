<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_id',
        'item_id',
        'quantity',
        'reason',
        'status',
        'customer_name',
        'order_reference',
        'covered_by_order',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get pending damage claims for a specific customer.
     */
    public static function pendingForCustomer(string $customerName)
    {
        return static::with('inventoryItem')
            ->where('status', 'Pending')
            ->whereRaw('LOWER(customer_name) = ?', [strtolower($customerName)])
            ->get();
    }

    /**
     * Generate damage claim ID like DC-2026-0001
     */
    public static function generateReturnId(): string
    {
        $prefix = 'DC';
        $year = date('Y');
        $last = static::where('return_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($last && $last->return_id) {
            $parts = explode('-', $last->return_id);
            $nextNum = intval(end($parts)) + 1;
        }

        return "{$prefix}-{$year}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
