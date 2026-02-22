<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer',
        'items',
        'address',
        'driver',
        'vehicle',
        'dispatch_time',
        'delivery_time',
        'status',
        'date',
        'assigned_by',
    ];

    protected $casts = [
        'dispatch_time' => 'datetime',
        'delivery_time' => 'datetime',
        'date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
