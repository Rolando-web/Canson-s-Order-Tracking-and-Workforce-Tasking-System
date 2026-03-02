<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

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
        $myAssignments = Assignment::with(['order.items', 'orderItem'])
            ->where('employee_id', $user->id)
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'completed', 'cancelled')")
            ->orderBy('assigned_date', 'desc')
            ->get()
            ->map(function ($a) {
                $order = $a->order;

                // If this is a per-item assignment, only show that item
                if ($a->order_item_id && $a->orderItem) {
                    $assignedItem = $a->orderItem;
                    $orderItems = [[
                        'id'            => $assignedItem->id,
                        'name'          => $assignedItem->name,
                        'quantity'      => $assignedItem->quantity,
                        'completed_qty' => $assignedItem->completed_qty ?? 0,
                        'remaining'     => $assignedItem->quantity - ($assignedItem->completed_qty ?? 0),
                    ]];
                    $itemsLabel = $assignedItem->quantity . ' ' . $assignedItem->name;
                } else {
                    $orderItems = $order ? $order->items->map(fn($i) => [
                        'id'            => $i->id,
                        'name'          => $i->name,
                        'quantity'      => $i->quantity,
                        'completed_qty' => $i->completed_qty ?? 0,
                        'remaining'     => $i->quantity - ($i->completed_qty ?? 0),
                    ])->toArray() : [];
                    $itemsLabel = $order ? $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ') : '';
                }

                return [
                    'id'               => $a->id,
                    'order_id'         => $a->order_id,
                    'order_item_id'    => $a->order_item_id,
                    'customer'         => $order ? $order->customer_name : 'N/A',
                    'customer_contact' => $order ? $order->contact_number : '',
                    'items'            => $itemsLabel,
                    'order_items'      => $orderItems,
                    'delivery_address' => $order ? $order->delivery_address : '',
                    'delivery_date'    => $order ? $order->delivery_date->format('Y-m-d') : '',
                    'total_amount'     => $order ? $order->total_amount : 0,
                    'priority'         => $a->priority,
                    'status'           => $a->status,
                    'order_status'     => $order ? $order->status : 'Pending',
                    'notes'            => $a->notes,
                    'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                    'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                ];
            })->toArray();

        $newAssignmentCount = Assignment::where('employee_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('pages.assignments-employee', compact('myAssignments', 'newAssignmentCount'));
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

            if ($activeCount > 0) {
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
                'active'      => $activeCount,
            ];
        });

        // Get assignments grouped by employee
        $assignmentsData = [];
        foreach ($employees as $emp) {
            $empAssignments = Assignment::with(['order.items', 'orderItem'])
                ->where('employee_id', $emp->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('assigned_date', 'desc')
                ->get()
                ->map(function ($a) {
                    $order = $a->order;
                    $assignedItem = $a->orderItem
                        ? "{$a->orderItem->quantity}x {$a->orderItem->name}"
                        : null;
                    return [
                        'id'               => $a->id,
                        'order_id'         => $a->order_id,
                        'order_item_id'    => $a->order_item_id,
                        'assigned_item'    => $assignedItem,
                        'customer'         => $order ? $order->customer_name : 'N/A',
                        'customer_contact' => $order ? $order->contact_number : '',
                        'items'            => $assignedItem ?? ($order ? $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ') : ''),
                        'delivery_address' => $order ? $order->delivery_address : '',
                        'delivery_date'    => $order ? $order->delivery_date->format('Y-m-d') : '',
                        'total_amount'     => $order ? $order->total_amount : 0,
                        'priority'         => $a->priority,
                        'status'           => $a->status,
                        'order_status'     => $order ? $order->status : 'Pending',
                        'notes'            => $a->notes,
                        'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                        'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                    ];
                })->toArray();

            $assignmentsData[$emp->name] = $empAssignments;
        }

        // Get unassigned orders
        $assignedOrderIds = Assignment::whereIn('status', ['pending', 'in_progress'])
            ->pluck('order_id')
            ->toArray();

        $availableOrders = Order::whereNotIn('order_id', $assignedOrderIds)
            ->whereNotIn('status', ['Ready for Delivery', 'Delivered'])
            ->with('items')
            ->get()
            ->map(function ($order) {
                return [
                    'order_id'         => $order->order_id,
                    'customer'         => $order->customer_name,
                    'customer_contact' => $order->contact_number,
                    'priority'         => strtolower($order->priority ?? 'normal'),
                    'items'            => $order->items->map(fn($i) => $i->quantity . 'x ' . $i->name)->implode(', '),
                    'delivery_address' => $order->delivery_address,
                    'delivery_date'    => $order->delivery_date->format('Y-m-d'),
                    'total_amount'     => (float) $order->total_amount,
                    'notes'            => $order->notes,
                    'order_items'      => $order->items->map(fn($i) => [
                        'id'       => $i->id,
                        'name'     => $i->name,
                        'quantity' => $i->quantity,
                        'price'    => (float) $i->unit_price,
                    ])->toArray(),
                ];
            })->toArray();

        return view('pages.assignments', compact('workers', 'assignmentsData', 'availableOrders'));
    }

    public function store(Request $request)
    {
        // ── Per-item assignment (multiple products → multiple employees) ──
        if ($request->has('item_assignments')) {
            $validated = $request->validate([
                'order_id'                         => 'required|string|exists:orders,order_id',
                'priority'                         => 'required|in:normal,high,urgent',
                'notes'                            => 'nullable|string',
                'item_assignments'                 => 'required|array|min:1',
                'item_assignments.*.order_item_id' => 'required|exists:order_items,id',
                'item_assignments.*.employee_id'   => 'required|exists:users,id',
            ]);

            $order = Order::where('order_id', $validated['order_id'])->first();

            DB::beginTransaction();
            try {
                $employeesById = [];
                $createdAssignments = [];

                foreach ($validated['item_assignments'] as $ia) {
                    $employee = User::find($ia['employee_id']);
                    if (!$employee) continue;

                    $assignment = Assignment::create([
                        'order_id'      => $validated['order_id'],
                        'order_item_id' => $ia['order_item_id'],
                        'employee_id'   => $ia['employee_id'],
                        'priority'      => $validated['priority'],
                        'status'        => 'pending',
                        'notes'         => $validated['notes'] ?? null,
                        'assigned_by'   => auth()->id(),
                        'assigned_date' => now()->toDateString(),
                    ]);

                    $createdAssignments[] = $assignment;
                    $employeesById[$employee->id] = $employee;

                    // Deduct stock for this specific item
                    $orderItem = OrderItem::find($ia['order_item_id']);
                    if ($orderItem) {
                        $inventoryItem = InventoryItem::find($orderItem->inventory_item_id);
                        if ($inventoryItem) {
                            $previousStock = $inventoryItem->stock;
                            $newStock      = max(0, $previousStock - $orderItem->quantity);

                            $inventoryItem->update([
                                'stock'  => $newStock,
                                'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
                            ]);

                            StockTransaction::create([
                                'item_id'          => $inventoryItem->id,
                                'transaction_type' => 'stock_out',
                                'quantity'         => $orderItem->quantity,
                                'previous_stock'   => $previousStock,
                                'new_stock'        => $newStock,
                                'reference_number' => StockTransaction::generateReference('stock_out'),
                                'reason'           => 'Order Assignment',
                                'notes'            => "Auto stock out for order {$validated['order_id']} – {$orderItem->name} assigned to {$employee->name}",
                                'transaction_date' => now()->toDateString(),
                                'created_by'       => auth()->id(),
                                'created_at'       => now(),
                            ]);
                        }
                    }
                }

                // Update order status
                if ($order) {
                    $assignedNames = count($employeesById) > 1
                        ? 'Multiple'
                        : (count($employeesById) === 1 ? array_values($employeesById)[0]->name : null);

                    $order->update([
                        'assigned' => $assignedNames,
                        'status'   => 'In-Progress',
                    ]);
                }

                // Notify each unique employee
                foreach ($employeesById as $empId => $employee) {
                    // Which items are assigned to this employee?
                    $empItemNames = collect($validated['item_assignments'])
                        ->where('employee_id', $empId)
                        ->map(fn($ia) => OrderItem::find($ia['order_item_id'])?->name)
                        ->filter()
                        ->implode(', ');

                    Notification::send(
                        $empId,
                        'work_assigned',
                        'New Work Assigned',
                        "You have been assigned to work on order {$validated['order_id']} – Items: {$empItemNames}. Priority: {$validated['priority']}.",
                        ['order_id' => $validated['order_id']]
                    );
                }

                DB::commit();

                if ($request->expectsJson()) {
                    return response()->json(['success' => true]);
                }
                return redirect()->back()->with('success', 'Assignments created successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
            }
        }

        // ── Legacy: assign whole order to a single employee ──
        $validated = $request->validate([
            'order_id'    => 'required|string',
            'employee_id' => 'required|exists:users,id',
            'priority'    => 'required|in:normal,high,urgent',
            'notes'       => 'nullable|string',
        ]);

        $employee = User::findOrFail($validated['employee_id']);

        $assignment = Assignment::create([
            'order_id'      => $validated['order_id'],
            'order_item_id' => null,
            'employee_id'   => $validated['employee_id'],
            'priority'      => $validated['priority'],
            'status'        => 'pending',
            'notes'         => $validated['notes'] ?? null,
            'assigned_by'   => auth()->id(),
            'assigned_date' => now()->toDateString(),
        ]);

        // Update order's assigned field and set status to In-Progress
        $order = Order::where('order_id', $validated['order_id'])->first();
        if ($order) {
            $order->update([
                'assigned' => $employee->name,
                'status'   => 'In-Progress',
            ]);

            // Automatic stock out for all items in this order
            foreach ($order->items as $orderItem) {
                $inventoryItem = InventoryItem::find($orderItem->inventory_item_id);
                if ($inventoryItem) {
                    $previousStock = $inventoryItem->stock;
                    $newStock = max(0, $previousStock - $orderItem->quantity);

                    $inventoryItem->update([
                        'stock'  => $newStock,
                        'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
                    ]);

                    StockTransaction::create([
                        'item_id'          => $inventoryItem->id,
                        'transaction_type' => 'stock_out',
                        'quantity'         => $orderItem->quantity,
                        'previous_stock'   => $previousStock,
                        'new_stock'        => $newStock,
                        'reference_number' => StockTransaction::generateReference('stock_out'),
                        'reason'           => 'Order Assignment',
                        'notes'            => "Auto stock out for order {$validated['order_id']} assigned to {$employee->name}",
                        'transaction_date' => now()->toDateString(),
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                    ]);
                }
            }
        }

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

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'assignment' => $assignment]);
        }

        return redirect()->back()->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $orderId = $assignment->order_id;
        $assignment->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }

    /**
     * Employee updates their own assignment status (pending -> in_progress -> completed)
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

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    /**
     * Employee updates their progress on order items (e.g., completed 150 out of 250)
     */
    public function updateProgress(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'items'         => 'required|array',
            'items.*.id'    => 'required|exists:order_items,id',
            'items.*.add_qty' => 'required|integer|min:1',
        ]);

        $assignment = Assignment::findOrFail($validated['assignment_id']);

        // Only allow the assigned employee to update
        if ($assignment->employee_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $order = Order::where('order_id', $assignment->order_id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Update each order item's completed_qty by ADDING the new input
        foreach ($validated['items'] as $itemData) {
            $orderItem = OrderItem::where('id', $itemData['id'])
                ->where('order_id', $order->id)
                ->first();

            if ($orderItem) {
                $newTotal = min($orderItem->completed_qty + $itemData['add_qty'], $orderItem->quantity);
                $orderItem->update(['completed_qty' => $newTotal]);
            }
        }

        // Check if ALL order items are fully completed (for order readiness)
        $order->refresh();
        $allDone = $order->items->every(fn($i) => $i->completed_qty >= $i->quantity);

        if ($allDone) {
            // Mark order as Ready for Delivery
            $order->update(['status' => 'Ready for Delivery']);

            // Mark ALL active assignments for this order as completed
            Assignment::where('order_id', $order->order_id)
                ->whereNotIn('status', ['cancelled'])
                ->update(['status' => 'completed']);

            // Notify managers
            $managerIds = User::where('role', 'admin')->pluck('id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'order_ready',
                'Order Ready for Delivery',
                "Order {$order->order_id} is ready for delivery. All items have been completed by {$user->name}.",
                ['order_id' => $order->order_id]
            );
        } else {
            // Check if THIS employee's assigned item is done (per-item assignment)
            if ($assignment->order_item_id) {
                $assignedItem = $order->items->firstWhere('id', $assignment->order_item_id);
                $thisDone = $assignedItem && $assignedItem->completed_qty >= $assignedItem->quantity;
                $assignment->update(['status' => $thisDone ? 'completed' : 'in_progress']);
            } else {
                $assignment->update(['status' => 'in_progress']);
            }
        }

        return response()->json([
            'success'       => true,
            'all_completed' => $allDone,
            'order_status'  => $order->fresh()->status,
        ]);
    }
}
