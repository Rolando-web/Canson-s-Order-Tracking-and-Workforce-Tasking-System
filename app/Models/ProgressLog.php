<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressLog extends Model
{
    protected $fillable = [
        'assignment_id',
        'phase_item_id',
        'employee_id',
        'qty_added',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'User_Id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'Assignment_Id');
    }

    public function phaseItem()
    {
        return $this->belongsTo(OrderPhaseItem::class, 'phase_item_id', 'Phase_Item_Id');
    }
}
