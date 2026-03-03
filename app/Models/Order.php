<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'Order_Id';

    protected $fillable = [
        'order_number',
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
        'total_amount'  => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'Order_Id');
    }

    public function phases()
    {
        return $this->hasMany(OrderPhase::class, 'order_id', 'Order_Id')->orderBy('phase_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_Id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'order_number', 'order_number');
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class, 'order_id', 'Order_Id');
    }

    public function hasPhases(): bool
    {
        return $this->phases()->exists();
    }

    /**
     * Overall completion % across all phases (or zero if no phases).
     */
    public function getOverallProgressAttribute(): int
    {
        $phases = $this->phases()->with('items')->get();
        $total  = $phases->sum(fn($p) => $p->items->sum('required_qty'));
        $done   = $phases->sum(fn($p) => $p->items->sum('completed_qty'));
        if ($total === 0) return 0;
        return (int) round(($done / $total) * 100);
    }

    public static function generateOrderId(): string
    {
        $last    = static::orderBy('Order_Id', 'desc')->first();
        $nextNum = $last ? intval(str_replace('ORD-', '', $last->order_number)) + 1 : 1;
        return 'ORD-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}