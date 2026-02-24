<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dispatch;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index()
    {
        $inventoryItems = InventoryItem::select('id', 'name', 'item_id', 'stock', 'unit', 'unit_price')
            ->orderBy('name')
            ->get();

        $orders = Order::with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $priorityColors = [
                    'Normal' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'High'   => 'bg-amber-50 text-amber-600 border-amber-200',
                    'Urgent' => 'bg-red-50 text-red-600 border-red-200',
                ];

                return [
                    'id'            => $order->order_id,
                    'db_id'         => $order->id,
                    'customer'      => $order->customer_name,
                    'contact'       => $order->contact_number,
                    'address'       => $order->delivery_address,
                    'items'         => $order->items->map(fn($i) => $i->name)->implode(', '),
                    'total_qty'     => $order->items->sum('quantity'),
                    'delivery_date' => $order->delivery_date->format('Y-m-d'),
                    'total'         => $order->total_amount,
                    'status'        => $order->status,
                    'assigned'      => $order->assigned,
                    'initial'       => $order->assigned ? strtoupper(substr($order->assigned, 0, 1)) : null,
                    'priority'      => $order->priority,
                    'priorityColor' => $priorityColors[$order->priority] ?? $priorityColors['Normal'],
                    'notes'         => $order->notes,
                    'order_items'   => $order->items->map(fn($i) => [
                        'name'     => $i->name,
                        'qty'      => $i->quantity,
                        'price'    => $i->unit_price,
                        'subtotal' => $i->subtotal,
                    ])->toArray(),
                ];
            });

        return view('pages.orders', compact('inventoryItems', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string|max:100',
            'contact_number'  => 'required|string|max:11',
            'delivery_address'=> 'required|string',
            'delivery_date'   => 'required|date',
            'priority'        => 'required|in:Normal,High,Urgent',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.name'    => 'required|string',
            'items.*.qty'     => 'required|integer|min:1',
            'items.*.price'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['qty'] * $item['price'];
            }

            $order = Order::create([
                'order_id'         => Order::generateOrderId(),
                'customer_name'    => $validated['customer_name'],
                'contact_number'   => $validated['contact_number'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_date'    => $validated['delivery_date'],
                'total_amount'     => $totalAmount,
                'status'           => 'Pending',
                'priority'         => $validated['priority'],
                'notes'            => $validated['notes'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                // Find inventory item by name
                $inventoryItem = InventoryItem::where('name', $item['name'])->first();

                OrderItem::create([
                    'order_id'          => $order->id,
                    'inventory_item_id' => $inventoryItem ? $inventoryItem->id : 0,
                    'name'              => $item['name'],
                    'quantity'          => $item['qty'],
                    'unit_price'        => $item['price'],
                    'subtotal'          => $item['qty'] * $item['price'],
                ]);

                // Deduct stock immediately since order is already paid
                if ($inventoryItem) {
                    $inventoryItem->stock = max(0, $inventoryItem->stock - $item['qty']);
                    $inventoryItem->status = $inventoryItem->stock > 0
                        ? ($inventoryItem->stock < 50 ? 'Low Stock' : 'In Stock')
                        : 'Out of Stock';
                    $inventoryItem->save();
                }
            }

            ActivityLog::log('Create Order', "Created order {$order->order_id} for {$order->customer_name} worth ₱" . number_format($totalAmount, 2));

            // Auto-create dispatch record for delivery
            $itemNames = collect($validated['items'])->map(function ($i) {
                return $i['qty'] . ' ' . $i['name'];
            })->implode(', ');

            Dispatch::create([
                'order_id'  => $order->id,
                'customer'  => $order->customer_name,
                'items'     => $itemNames,
                'address'   => $order->delivery_address,
                'status'    => 'pending',
                'date'      => $order->delivery_date,
            ]);

            DB::commit();

            // Notify all admin managers about the new order
            $managerIds = User::where('role', 'admin')->pluck('id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'new_order',
                'New Order Created',
                "Order {$order->order_id} for {$order->customer_name} worth ₱" . number_format($totalAmount, 2) . " needs to be assigned to a worker.",
                ['order_id' => $order->order_id]
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'order' => $order->load('items')]);
            }

            return redirect()->back()->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status'   => 'sometimes|in:Pending,In-Progress,Completed',
            'assigned' => 'sometimes|nullable|string|max:100',
            'priority' => 'sometimes|in:Normal,High,Urgent',
            'notes'    => 'sometimes|nullable|string',
        ]);

        $oldStatus = $order->status;
        $order->update($validated);

        if (isset($validated['status'])) {
            ActivityLog::log('Update Order', "Order {$order->order_id} status changed from {$oldStatus} to {$validated['status']}");
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'order' => $order]);
        }

        return redirect()->back()->with('success', 'Order updated successfully.');
    }

    public function destroy(Request $request, Order $order)
    {
        $orderId = $order->order_id;
        $order->delete();

        ActivityLog::log('Delete Order', "Deleted order {$orderId}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Order deleted successfully.');
    }
}
