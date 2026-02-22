<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_name',
        'contact_number',
        'delivery_address',
        'delivery_date',
        'total_amount',
        'status',
        'priority',
        'assigned',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function dispatch()
    {
        return $this->hasOne(Dispatch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'order_id', 'order_id');
    }

    /**
     * Generate next order ID like ORD-001, ORD-002, etc.
     */
    public static function generateOrderId(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $nextNum = $last ? intval(str_replace('ORD-', '', $last->order_id)) + 1 : 1;
        return 'ORD-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}
