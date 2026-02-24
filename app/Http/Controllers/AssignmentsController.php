<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\User;
use App\Models\Dispatch;
use App\Models\ActivityLog;
use App\Models\Notification;

class AssignmentsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // If the user is an employee, show only their own assignments
        if ($user->isEmployee()) {
            return $this->employeeView($user);
        }

        return $this->adminView();
    }

    /**
     * Employee view — only shows their own assignments
     */
    private function employeeView($user)
    {
        $myAssignments = Assignment::with('order')
            ->where('employee_id', $user->id)
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'completed', 'cancelled')")
            ->orderBy('assigned_date', 'desc')
            ->get()
            ->map(function ($a) {
                $order = $a->order;
                return [
                    'id'               => $a->id,
                    'order_id'         => $a->order_id,
                    'customer'         => $order ? $order->customer_name : 'N/A',
                    'customer_contact' => $order ? $order->contact_number : '',
                    'items'            => $order ? $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ') : '',
                    'delivery_address' => $order ? $order->delivery_address : '',
                    'delivery_date'    => $order ? $order->delivery_date->format('Y-m-d') : '',
                    'total_amount'     => $order ? $order->total_amount : 0,
                    'priority'         => $a->priority,
                    'status'           => $a->status,
                    'notes'            => $a->notes,
                    'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                    'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                ];
            })->toArray();

        $newAssignmentCount = Assignment::where('employee_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $department = $user->department ?? 'Worker';

        // For Drivers, also fetch their delivery assignments
        $myDeliveries = [];
        if ($department === 'Driver') {
            $myDeliveries = Dispatch::with('order')
                ->where('driver', $user->name)
                ->orderByRaw("FIELD(status, 'in_transit', 'pending', 'delivered')")
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($d) {
                    $order = $d->order;
                    return [
                        'id'               => $d->id,
                        'order_id'         => $order ? $order->order_id : 'N/A',
                        'customer'         => $d->customer,
                        'items'            => $d->items,
                        'delivery_address' => $d->address,
                        'delivery_date'    => $d->date->format('Y-m-d'),
                        'total_amount'     => $order ? $order->total_amount : 0,
                        'vehicle'          => $d->vehicle ?? 'Not assigned',
                        'status'           => $d->status,
                        'dispatch_time'    => $d->dispatch_time ? $d->dispatch_time->format('Y-m-d H:i') : null,
                        'delivery_time'    => $d->delivery_time ? $d->delivery_time->format('Y-m-d H:i') : null,
                    ];
                })->toArray();
        }

        return view('pages.assignments-employee', compact('myAssignments', 'myDeliveries', 'newAssignmentCount', 'department'));
    }

    /**
     * Admin/Super Admin view — full workforce management
     */
    private function adminView()
    {
        $colors = ['bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];

        // Get all employees
        $employees = User::where('role', 'employee')->get();
        $workers = $employees->map(function ($emp, $index) use ($colors) {
            $activeCount = Assignment::where('employee_id', $emp->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            // Check if driver is currently on a delivery (in_transit)
            $onDelivery = false;
            if (($emp->department ?? 'Worker') === 'Driver') {
                $onDelivery = Dispatch::where('driver', $emp->name)
                    ->where('status', 'in_transit')
                    ->exists();
            }

            if ($onDelivery) {
                $status = 'ON DELIVERY';
                $statusColor = 'bg-purple-100 text-purple-700';
            } elseif ($activeCount > 0) {
                $status = 'BUSY';
                $statusColor = 'bg-amber-100 text-amber-700';
            } else {
                $status = 'AVAILABLE';
                $statusColor = 'bg-green-100 text-green-700';
            }

            return [
                'id'          => $emp->id,
                'name'        => $emp->name,
                'initial'     => $emp->initial,
                'color'       => $colors[$index % count($colors)],
                'status'      => $status,
                'statusColor' => $statusColor,
                'dept'        => $emp->department ?? 'Worker',
                'active'      => $activeCount,
                'onDelivery'  => $onDelivery,
            ];
        });

        // Get only Drivers for the delivery assignment dropdown
        $drivers = $workers->filter(fn($w) => $w['dept'] === 'Driver')->values();

        // Get assignments grouped by employee
        $assignmentsData = [];
        foreach ($employees as $emp) {
            $empAssignments = Assignment::with('order')
                ->where('employee_id', $emp->id)
                ->orderBy('assigned_date', 'desc')
                ->get()
                ->map(function ($a) {
                    $order = $a->order;
                    return [
                        'id'               => $a->id,
                        'order_id'         => $a->order_id,
                        'customer'         => $order ? $order->customer_name : 'N/A',
                        'customer_contact' => $order ? $order->contact_number : '',
                        'items'            => $order ? $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ') : '',
                        'delivery_address' => $order ? $order->delivery_address : '',
                        'delivery_date'    => $order ? $order->delivery_date->format('Y-m-d') : '',
                        'total_amount'     => $order ? $order->total_amount : 0,
                        'priority'         => $a->priority,
                        'status'           => $a->status,
                        'notes'            => $a->notes,
                        'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                        'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                    ];
                })->toArray();

            $assignmentsData[$emp->name] = $empAssignments;
        }

        // Get unassigned orders (orders without assignments or with status Pending)
        $assignedOrderIds = Assignment::whereIn('status', ['pending', 'in_progress'])
            ->pluck('order_id')
            ->toArray();

        $availableOrders = Order::whereNotIn('order_id', $assignedOrderIds)
            ->where('status', '!=', 'Completed')
            ->with('items')
            ->get()
            ->map(function ($order) {
                return [
                    'order_id'         => $order->order_id,
                    'customer'         => $order->customer_name,
                    'customer_contact' => $order->contact_number,
                    'items'            => $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', '),
                    'delivery_address' => $order->delivery_address,
                    'delivery_date'    => $order->delivery_date->format('Y-m-d'),
                    'total_amount'     => $order->total_amount,
                ];
            })->toArray();

        return view('pages.assignments', compact('workers', 'drivers', 'assignmentsData', 'availableOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'    => 'required|string',
            'employee_id' => 'required|exists:users,id',
            'priority'    => 'required|in:normal,high,urgent',
            'notes'       => 'nullable|string',
        ]);

        $employee = User::findOrFail($validated['employee_id']);

        // If the employee is a Driver, check they are not currently on a delivery
        if (($employee->department ?? 'Worker') === 'Driver') {
            $onDelivery = Dispatch::where('driver', $employee->name)
                ->where('status', 'in_transit')
                ->exists();

            if ($onDelivery) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $employee->name . ' is currently on a delivery. Cannot assign work until delivery is completed.',
                    ], 422);
                }
                return redirect()->back()->with('error', $employee->name . ' is currently on a delivery.');
            }
        }

        $assignment = Assignment::create([
            'order_id'      => $validated['order_id'],
            'employee_id'   => $validated['employee_id'],
            'priority'      => $validated['priority'],
            'status'        => 'pending',
            'notes'         => $validated['notes'] ?? null,
            'assigned_by'   => auth()->id(),
            'assigned_date' => now()->toDateString(),
        ]);

        // Update order's assigned field
        $order = Order::where('order_id', $validated['order_id'])->first();
        if ($order) {
            $order->update(['assigned' => $employee->name]);
        }

        ActivityLog::log('Assign Task', "Assigned order {$validated['order_id']} to {$employee->name}");

        // Notify the employee about the new work assignment
        Notification::send(
            $employee->id,
            'work_assigned',
            'New Work Assigned',
            "You have been assigned to work on order {$validated['order_id']}. Priority: {$validated['priority']}.",
            ['order_id' => $validated['order_id'], 'assignment_id' => $assignment->id]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'assignment' => $assignment]);
        }

        return redirect()->back()->with('success', 'Assignment created successfully.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'status'   => 'sometimes|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|in:normal,high,urgent',
            'notes'    => 'sometimes|nullable|string',
        ]);

        $assignment->update($validated);

        // When assignment is marked as completed, ensure a dispatch record exists for delivery
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $order = Order::where('order_id', $assignment->order_id)->first();

            if ($order) {
                // Check if a dispatch record already exists for this order
                $existingDispatch = Dispatch::where('order_id', $order->id)->first();

                if (!$existingDispatch) {
                    // Build items string from order items
                    $itemNames = $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ');

                    Dispatch::create([
                        'order_id' => $order->id,
                        'customer' => $order->customer_name,
                        'items'    => $itemNames,
                        'address'  => $order->delivery_address,
                        'status'   => 'pending',
                        'date'     => $order->delivery_date,
                    ]);
                }
            }
        }

        ActivityLog::log('Update Assignment', "Updated assignment #{$assignment->id} for order {$assignment->order_id}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'assignment' => $assignment]);
        }

        return redirect()->back()->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $orderId = $assignment->order_id;
        $assignment->delete();

        ActivityLog::log('Remove Assignment', "Removed assignment for order {$orderId}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }

    /**
     * Employee updates their own assignment status (pending → in_progress → completed)
     */
    public function updateStatus(Request $request, Assignment $assignment)
    {
        $user = auth()->user();

        // Only allow employees to update their own assignments
        if ($assignment->employee_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $assignment->update(['status' => $validated['status']]);

        // When assignment is marked as completed, create a dispatch record
        if ($validated['status'] === 'completed') {
            $order = Order::where('order_id', $assignment->order_id)->first();
            if ($order) {
                $existingDispatch = Dispatch::where('order_id', $order->id)->first();
                if (!$existingDispatch) {
                    $itemNames = $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ');
                    Dispatch::create([
                        'order_id' => $order->id,
                        'customer' => $order->customer_name,
                        'items'    => $itemNames,
                        'address'  => $order->delivery_address,
                        'status'   => 'pending',
                        'date'     => $order->delivery_date,
                    ]);
                }
            }
        }

        ActivityLog::log('Update Assignment Status', "Employee updated assignment #{$assignment->id} to {$validated['status']}");

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    /**
     * Driver updates their own delivery status (ship / deliver).
     */
    public function updateDeliveryStatus(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'dispatch_id' => 'required|integer|exists:dispatches,id',
            'action'      => 'required|in:ship,deliver',
        ]);

        $dispatch = Dispatch::findOrFail($validated['dispatch_id']);

        // Only allow the assigned driver to update
        if ($dispatch->driver !== $user->name) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($validated['action'] === 'ship') {
            $dispatch->update([
                'status'        => 'in_transit',
                'dispatch_time' => now(),
            ]);
            ActivityLog::log('Start Delivery', "Driver {$user->name} started delivery for dispatch #{$dispatch->id}");
        } elseif ($validated['action'] === 'deliver') {
            $dispatch->update([
                'status'        => 'delivered',
                'delivery_time' => now(),
            ]);
            ActivityLog::log('Complete Delivery', "Driver {$user->name} completed delivery for dispatch #{$dispatch->id}");
        }

        return response()->json(['success' => true, 'dispatch' => $dispatch]);
    }
}
