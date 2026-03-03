<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $primaryKey = 'Supplier_Id';

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'archived',
    ];

    protected $casts = [
        'archived' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'supplier_id', 'Supplier_Id');
    }
}