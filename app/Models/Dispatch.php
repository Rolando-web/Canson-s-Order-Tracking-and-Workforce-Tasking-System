<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $primaryKey = 'Dispatch_Id';

    public $timestamps = false;

    protected $fillable = [
        'phase_id',
        'dispatched_at',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'created_at'    => 'datetime',
    ];

    public function phase()
    {
        return $this->belongsTo(OrderPhase::class, 'phase_id', 'Phase_Id');
    }
}
