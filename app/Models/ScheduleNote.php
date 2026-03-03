<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleNote extends Model
{
    use HasFactory;

    protected $primaryKey = 'Schedule_Note_Id';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'schedule_date',
        'start_time',
        'end_time',
        'priority',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_Id');
    }
}