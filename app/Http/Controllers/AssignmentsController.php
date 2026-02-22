<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\User;
use App\Models\ActivityLog;

class AssignmentsController extends Controller
{
    public function index()
    {
        $colors = ['bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];

        // Get all employees as workers
        $employees = User::where('role', 'employee')->get();
        $workers = $employees->map(function ($emp, $index) use ($colors) {
            $activeCount = Assignment::where('employee_id', $emp->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            $status = $activeCount > 0 ? 'BUSY' : 'AVAILABLE';
            $statusColor = $activeCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700';

            return [
                'id'          => $emp->id,
                'name'        => $emp->name,
                'initial'     => $emp->initial,
                'color'       => $colors[$index % count($colors)],
                'status'      => $status,
                'statusColor' => $statusColor,
                'dept'        => 'General',
                'active'      => $activeCount,
            ];
        });

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

        return view('pages.assignments', compact('workers', 'assignmentsData', 'availableOrders'));
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
}
