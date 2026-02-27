<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Notification;
use App\Models\User;

class DispatchController extends Controller
{
    public function index()
    {
        // Get orders that are Ready for Delivery or already Delivered
        $orders = Order::with('items')
            ->whereIn('status', ['Ready for Delivery', 'Delivered'])
            ->orderByRaw("FIELD(status, 'Ready for Delivery', 'Delivered')")
            ->orderBy('delivery_date', 'asc')
            ->get()
            ->map(function ($order) {
                $itemNames = $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ');
                return [
                    'id'            => $order->id,
                    'order_id'      => $order->order_id,
                    'customer'      => $order->customer_name,
                    'items'         => $itemNames,
                    'address'       => $order->delivery_address,
                    'contact'       => $order->contact_number,
                    'total_amount'  => $order->total_amount,
                    'status'        => $order->status,
                    'delivery_date' => $order->delivery_date->format('Y-m-d'),
                    'delivered_at'  => $order->updated_at->format('M d, Y h:i A'),
                    'assigned'      => $order->assigned,
                ];
            });

        $readyCount    = $orders->where('status', 'Ready for Delivery')->count();
        $deliveredCount = $orders->where('status', 'Delivered')->count();

        return view('pages.dispatch', compact('orders', 'readyCount', 'deliveredCount'));
    }

    /**
     * Manager clicks "Deliver" → order status becomes Delivered.
     */
    public function deliver(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->status !== 'Ready for Delivery') {
            return response()->json(['success' => false, 'message' => 'Order is not ready for delivery.'], 422);
        }

        $order->update(['status' => 'Delivered']);

        // Notify the employee who worked on this order
        $assignment = $order->assignments()->first();
        if ($assignment) {
            Notification::send(
                $assignment->employee_id,
                'order_delivered',
                'Order Delivered',
                "Order {$order->order_id} for {$order->customer_name} has been marked as delivered.",
                ['order_id' => $order->order_id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Order marked as delivered.');
    }
}
