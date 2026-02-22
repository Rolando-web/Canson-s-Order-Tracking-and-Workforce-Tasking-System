<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispatch;
use App\Models\Order;
use App\Models\ActivityLog;

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

        return view('pages.dispatch', compact('dispatches', 'readyToShip', 'inTransit', 'delivered'));
    }

    public function assignDriver(Request $request)
    {
        $validated = $request->validate([
            'dispatch_id' => 'required|exists:dispatches,id',
            'driver'      => 'required|string|max:100',
            'vehicle'     => 'required|string|max:100',
            'action'      => 'sometimes|in:ship,deliver',
        ]);

        $dispatch = Dispatch::findOrFail($validated['dispatch_id']);
        $dispatch->driver = $validated['driver'];
        $dispatch->vehicle = $validated['vehicle'];

        $action = $validated['action'] ?? null;

        if ($action === 'ship') {
            $dispatch->status = 'in_transit';
            $dispatch->dispatch_time = now();
            $dispatch->assigned_by = auth()->id();
            ActivityLog::log('Dispatch Shipped', "Order {$dispatch->order?->order_id} dispatched with driver {$dispatch->driver}");
        } elseif ($action === 'deliver') {
            $dispatch->status = 'delivered';
            $dispatch->delivery_time = now();
            ActivityLog::log('Dispatch Delivered', "Order {$dispatch->order?->order_id} delivered by {$dispatch->driver}");
        } else {
            $dispatch->assigned_by = auth()->id();
            ActivityLog::log('Assign Driver', "Assigned driver {$dispatch->driver} to order {$dispatch->order?->order_id}");
        }

        $dispatch->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'dispatch' => $dispatch]);
        }

        return redirect()->back()->with('success', 'Dispatch updated successfully.');
    }
}
