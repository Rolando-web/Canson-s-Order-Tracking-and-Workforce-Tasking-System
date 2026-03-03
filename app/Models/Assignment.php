<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'Assignment_Id';

    protected $fillable = [
        'order_number',
        'order_item_id',
        'employee_id',
        'priority',
        'status',
        'notes',
        'assigned_by',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'Order_Item_Id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'User_Id');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'User_Id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_number', 'order_number');
    }
}