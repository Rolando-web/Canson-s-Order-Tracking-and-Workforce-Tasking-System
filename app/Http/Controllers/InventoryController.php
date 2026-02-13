<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('item_id')->get();
        $totalItems = $items->count();
        $lowStockAlert = $items->where('stock', '<', 50)->count();
        
        return view('pages.inventory', compact('items', 'totalItems', 'lowStockAlert'));
    }
}
