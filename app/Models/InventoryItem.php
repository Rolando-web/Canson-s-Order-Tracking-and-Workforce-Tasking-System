<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'item_id',
        'category',
        'stock',
        'unit',
        'status',
        'is_best_seller',
        'image_path',
    ];

    protected $casts = [
        'is_best_seller' => 'boolean',
        'stock' => 'integer',
    ];
}
