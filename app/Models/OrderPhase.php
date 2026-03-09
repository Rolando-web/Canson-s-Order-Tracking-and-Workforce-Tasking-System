<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPhase extends Model
{
    protected $primaryKey = 'Phase_Id';

    protected $fillable = [
        'order_id',
        'phase_number',
        'delivery_date',
        'status',
        'damage_qty',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'damage_qty'    => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'Order_Id');
    }

    public function items()
    {
        return $this->hasMany(OrderPhaseItem::class, 'phase_id', 'Phase_Id');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'phase_id', 'Phase_Id');
    }

    /** Overall completion percentage for this phase */
    public function getProgressPercentAttribute(): int
    {
        $total     = $this->items->sum('required_qty');
        $completed = $this->items->sum('completed_qty');
        if ($total === 0) return 0;
        return (int) round(($completed / $total) * 100);
    }
}