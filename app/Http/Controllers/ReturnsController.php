<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturnItem;

class ReturnsController extends Controller
{
    public function index()
    {
        $claims = ReturnItem::with(['inventoryItem', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'pending' => $claims->where('status', 'Pending')->count(),
            'covered' => $claims->where('status', 'Covered')->count(),
            'total'   => $claims->count(),
        ];

        return view('pages.returns', compact('claims', 'stats'));
    }

    public function pendingForCustomer(Request $request)
    {
        $request->validate(['customer_name' => 'required|string']);

        $claims = ReturnItem::pendingForCustomer($request->customer_name);

        return response()->json([
            'claims' => $claims->map(function ($c) {
                return [
                    'id'          => $c->Return_Id,
                    'return_id'   => $c->return_number,
                    'item_id'     => $c->product_id,
                    'item_name'   => $c->inventoryItem->name ?? 'N/A',
                    'quantity'    => $c->quantity,
                    'reason'      => $c->reason,
                    'order_ref'   => $c->order_reference,
                    'date'        => $c->created_at->format('M d, Y'),
                ];
            }),
        ]);
    }
}