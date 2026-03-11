<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPhaseItem extends Model
{
    protected $primaryKey = 'Phase_Item_Id';

    protected $fillable = [
        'phase_id',
        'product_id',
        'name',
        'base_qty',
        'damage_carry',
        'required_qty',
        'completed_qty',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'base_qty'      => 'integer',
        'damage_carry'  => 'integer',
        'required_qty'  => 'integer',
        'completed_qty' => 'integer',
        'unit_price'    => 'decimal:2',
        'subtotal'      => 'decimal:2',
    ];

    public function phase()
    {
        return $this->belongsTo(OrderPhase::class, 'phase_id', 'Phase_Id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'Product_Id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->required_qty === 0) return 0;
        return (int) min(100, round(($this->completed_qty / $this->required_qty) * 100));
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->required_qty - $this->completed_qty);
    }
}