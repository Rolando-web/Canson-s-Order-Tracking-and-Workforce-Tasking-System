<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'returns';
    protected $primaryKey = 'Return_Id';

    protected $fillable = [
        'return_number',
        'product_id',
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'Product_Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_Id');
    }

    public static function pendingForCustomer(string $customerName)
    {
        return static::with('product')
            ->where('status', 'Pending')
            ->whereRaw('LOWER(customer_name) = ?', [strtolower($customerName)])
            ->get();
    }

    public static function generateReturnId(): string
    {
        $prefix = 'DC';
        $year = date('Y');
        $last = static::where('return_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('Return_Id', 'desc')
            ->first();

        $nextNum = 1;
        if ($last && $last->return_number) {
            $parts = explode('-', $last->return_number);
            $nextNum = intval(end($parts)) + 1;
        }

        return "{$prefix}-{$year}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function getClaimIdAttribute(): ?string
    {
        if (!empty($this->order_reference)) {
            if (preg_match('/(\d+)/', (string) $this->order_reference, $matches)) {
                return 'CLM-' . str_pad((string) intval($matches[1]), 3, '0', STR_PAD_LEFT);
            }

            return 'CLM-' . (string) $this->order_reference;
        }

        if (!empty($this->Return_Id)) {
            return 'CLM-' . str_pad((string) $this->Return_Id, 3, '0', STR_PAD_LEFT);
        }

        return null;
    }
}