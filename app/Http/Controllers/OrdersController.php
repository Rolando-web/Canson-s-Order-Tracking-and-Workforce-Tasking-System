<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class OrdersController extends Controller
{
    public function index()
    {
        $inventoryItems = InventoryItem::select('id', 'name', 'item_id', 'stock', 'unit')
            ->orderBy('name')
            ->get();

        return view('pages.orders', compact('inventoryItems'));
    }
}
