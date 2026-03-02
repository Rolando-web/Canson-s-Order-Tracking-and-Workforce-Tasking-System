<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Notification;
use App\Models\User;
use App\Models\ReturnItem;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

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

                // Check if this order covers any damage claims
                $coveredClaims = ReturnItem::where('covered_by_order', $order->order_id)->get();
                $coveredItemIds = $coveredClaims->pluck('item_id')->toArray();

                return [
                    'id'            => $order->id,
                    'order_id'      => $order->order_id,
                    'customer'      => $order->customer_name,
                    'items'         => $itemNames,
                    'item_details'  => $order->items->map(fn($i) => [
                        'inventory_item_id' => $i->inventory_item_id,
                        'name'              => $i->name,
                        'quantity'          => $i->quantity,
                        'unit_price'        => (float) $i->unit_price,
                        'is_cover'          => in_array($i->inventory_item_id, $coveredItemIds) && (float) $i->unit_price === 0.0,
                    ])->values(),
                    'has_cover_items' => $coveredClaims->count() > 0,
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
     * Optionally accepts damage reports to create damage claims and adjust stock.
     */
    public function deliver(Request $request)
    {
        $validated = $request->validate([
            'order_id'              => 'required|integer|exists:orders,id',
            'damages'               => 'nullable|array',
            'damages.*.item_id'     => 'required_with:damages|integer|exists:inventory_items,id',
            'damages.*.item_name'   => 'required_with:damages|string|max:255',
            'damages.*.quantity'    => 'required_with:damages|integer|min:1',
            'damages.*.reason'      => 'required_with:damages|string|max:255',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->status !== 'Ready for Delivery') {
            return response()->json(['success' => false, 'message' => 'Order is not ready for delivery.'], 422);
        }

        DB::beginTransaction();

        try {
            $order->update(['status' => 'Delivered']);

            // Process damage reports if any
            if (!empty($validated['damages'])) {
                foreach ($validated['damages'] as $damage) {
                    // Create damage claim
                    ReturnItem::create([
                        'return_id'       => ReturnItem::generateReturnId(),
                        'item_id'         => $damage['item_id'],
                        'quantity'        => $damage['quantity'],
                        'reason'          => $damage['reason'],
                        'status'          => 'Pending',
                        'customer_name'   => $order->customer_name,
                        'order_reference' => $order->order_id,
                        'created_by'      => auth()->id(),
                    ]);

                    // Adjust stock — deduct damaged quantity for transparency
                    $item = InventoryItem::find($damage['item_id']);
                    if ($item) {
                        $prevStock = $item->stock;
                        $item->decrement('stock', $damage['quantity']);

                        StockTransaction::create([
                            'item_id'           => $item->id,
                            'transaction_type'  => 'stock_out',
                            'quantity'          => $damage['quantity'],
                            'previous_stock'    => $prevStock,
                            'new_stock'         => $prevStock - $damage['quantity'],
                            'reason'            => 'Delivery Damage',
                            'reference_number'  => $order->order_id,
                            'notes'             => $damage['reason'],
                            'transaction_date'  => now(),
                            'created_by'        => auth()->id(),
                            'created_at'        => now(),
                        ]);
                    }
                }
            }

            DB::commit();

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

            $damageCount = count($validated['damages'] ?? []);
            $message = $damageCount > 0
                ? "Order delivered. {$damageCount} damage claim(s) recorded — replacement items will be included in the customer's next order."
                : 'Order marked as delivered.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delivery failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to process delivery: ' . $e->getMessage()], 500);
        }
    }
}
