<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispatch;
use App\Models\Order;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;

class DispatchController extends Controller
{
    public function index()
    {
        $dispatches = Dispatch::with('order')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id'             => $d->id,
                    'order_id'       => $d->order ? $d->order->order_id : 'N/A',
                    'customer'       => $d->customer,
                    'items'          => $d->items,
                    'address'        => $d->address,
                    'driver'         => $d->driver ?? 'Unassigned',
                    'driver_initial' => $d->driver ? strtoupper(substr($d->driver, 0, 1)) : '?',
                    'vehicle'        => $d->vehicle ?? 'Not assigned',
                    'dispatch_time'  => $d->dispatch_time ? $d->dispatch_time->format('Y-m-d H:i') : null,
                    'delivery_time'  => $d->delivery_time ? $d->delivery_time->format('Y-m-d H:i') : null,
                    'status'         => $d->status,
                    'assigned_by'    => $d->assignedByUser ? $d->assignedByUser->name : 'System',
                    'date'           => $d->date->format('Y-m-d'),
                ];
            });

        // Status counts
        $readyToShip = Dispatch::where('status', 'pending')->count();
        $inTransit   = Dispatch::where('status', 'in_transit')->count();
        $delivered   = Dispatch::where('status', 'delivered')->count();

        // Get only Drivers for delivery assignment dropdown
        $deliveryUsers = User::where('role', 'employee')
            ->where('department', 'Driver')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id'      => $u->id,
                    'name'    => $u->name,
                    'initial' => $u->initial,
                ];
            });

        return view('pages.dispatch', compact('dispatches', 'readyToShip', 'inTransit', 'delivered', 'deliveryUsers'));
    }

    public function assignDriver(Request $request)
    {
        $validated = $request->validate([
            'dispatch_id'      => 'required|exists:dispatches,id',
            'delivery_user_id' => 'sometimes|nullable|exists:users,id',
            'vehicle'          => 'nullable|string|max:100',
            'action'           => 'sometimes|in:ship,deliver',
        ]);

        $dispatch = Dispatch::findOrFail($validated['dispatch_id']);
        $action = $validated['action'] ?? null;

        // If assigning a new delivery user
        $deliveryUser = null;
        if (!empty($validated['delivery_user_id'])) {
            $deliveryUser = User::findOrFail($validated['delivery_user_id']);
            $dispatch->driver = $deliveryUser->name;
            $dispatch->assigned_by = auth()->id();
        }

        if (!empty($validated['vehicle'])) {
            $dispatch->vehicle = $validated['vehicle'];
        }

        if ($action === 'ship') {
            $dispatch->status = 'in_transit';
            $dispatch->dispatch_time = now();
            ActivityLog::log('Dispatch Shipped', "Order {$dispatch->order?->order_id} dispatched with delivery person {$dispatch->driver}");
        } elseif ($action === 'deliver') {
            $dispatch->status = 'delivered';
            $dispatch->delivery_time = now();
            ActivityLog::log('Dispatch Delivered', "Order {$dispatch->order?->order_id} delivered by {$dispatch->driver}");
        } else {
            ActivityLog::log('Assign Delivery', "Assigned delivery person {$dispatch->driver} to order {$dispatch->order?->order_id}");
        }

        $dispatch->save();

        // Notify the driver about the delivery assignment
        if ($deliveryUser && !$action) {
            Notification::send(
                $deliveryUser->id,
                'delivery_assigned',
                'New Delivery Assigned',
                "You have been assigned to deliver order {$dispatch->order?->order_id} to {$dispatch->customer}.",
                ['dispatch_id' => $dispatch->id, 'order_id' => $dispatch->order?->order_id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'dispatch' => $dispatch]);
        }

        return redirect()->back()->with('success', 'Dispatch updated successfully.');
    }
}
