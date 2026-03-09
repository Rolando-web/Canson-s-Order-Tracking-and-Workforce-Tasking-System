<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPhaseItem;
use App\Models\ProgressLog;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\StockOut;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class AssignmentsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isEmployee()) {
            return $this->employeeView($user);
        }

        return $this->adminView();
    }

    private function employeeView($user)
    {
        $myAssignments = Assignment::with(['order.items', 'orderItem', 'phase.items'])
            ->where('employee_id', $user->User_Id)
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'completed', 'cancelled')")
            ->orderBy('assigned_date', 'desc')
            ->get()
            ->map(function ($a) {
                $order = $a->order;

                if ($a->order_item_id && $a->orderItem) {
                    $assignedItem = $a->orderItem;

                    // If this assignment is phase-specific, use the phase's required_qty and completed_qty
                    $displayQty = $assignedItem->quantity;
                    $phaseCompleted = $assignedItem->completed_qty ?? 0;
                    if ($a->phase_id && $a->phase) {
                        $phaseItem = $a->phase->items->firstWhere('name', $assignedItem->name);
                        if ($phaseItem) {
                            $displayQty = $phaseItem->required_qty;
                            $phaseCompleted = $phaseItem->completed_qty;
                        }
                    }

                    $orderItems = [[
                        'id'            => $assignedItem->Order_Item_Id,
                        'name'          => $assignedItem->name,
                        'quantity'      => $displayQty,
                        'completed_qty' => $phaseCompleted,
                        'remaining'     => $displayQty - $phaseCompleted,
                    ]];
                    $itemsLabel = $displayQty . ' ' . $assignedItem->name;
                } else {
                    $orderItems = $order ? $order->items->map(fn($i) => [
                        'id'            => $i->Order_Item_Id,
                        'name'          => $i->name,
                        'quantity'      => $i->quantity,
                        'completed_qty' => $i->completed_qty ?? 0,
                        'remaining'     => $i->quantity - ($i->completed_qty ?? 0),
                    ])->toArray() : [];
                    $itemsLabel = $order ? $order->items->map(fn($i) => $i->quantity . ' ' . $i->name)->implode(', ') : '';
                }

                // Auto-complete if all items are already 100% done (e.g. after seeding or manual DB update)
                $assignmentStatus = $a->status;
                if ($assignmentStatus === 'in_progress' && !empty($orderItems)) {
                    $allDone = collect($orderItems)->every(fn($i) => $i['remaining'] <= 0);
                    if ($allDone) {
                        $a->update(['status' => 'completed']);
                        $assignmentStatus = 'completed';
                        if ($order) {
                            $stillOpen = Assignment::where('order_number', $order->order_number)
                                ->whereNotIn('status', ['completed', 'cancelled'])
                                ->count();
                            if ($stillOpen === 0 && $order->status === 'In-Progress') {
                                $order->update(['status' => 'Ready for Delivery']);
                            }
                        }
                    }
                }

                return [
                    'id'               => $a->Assignment_Id,
                    'order_id'         => $a->order_number,
                    'order_item_id'    => $a->order_item_id,
                    'customer'         => $order ? $order->customer_name : 'N/A',
                    'customer_contact' => $order ? $order->contact_number : '',
                    'items'            => $itemsLabel,
                    'order_items'      => $orderItems,
                    'delivery_address' => $order ? $order->delivery_address : '',
                    'delivery_date'    => $order ? $order->delivery_date->format('Y-m-d') : '',
                    'total_amount'     => $order ? $order->total_amount : 0,
                    'priority'         => $a->priority,
                    'status'           => $assignmentStatus,
                    'order_status'     => $order ? $order->fresh()->status : 'Pending',
                    'notes'            => $a->notes,
                    'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                    'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                    'phase_number'     => $a->phase ? $a->phase->phase_number : null,
                    'progress_history' => $this->loadProgressHistory($a),
                ];
            })->toArray();

        $newAssignmentCount = Assignment::where('employee_id', $user->User_Id)
            ->where('status', 'pending')
            ->count();

        return view('pages.assignments-employee', compact('myAssignments', 'newAssignmentCount'));
    }

    private function loadProgressHistory($assignment): array
    {
        if ($assignment->phase_id && $assignment->phase && $assignment->orderItem) {
            // For phase assignments: show all contributors to the same phase item
            $phaseItem = $assignment->phase->items->firstWhere('name', $assignment->orderItem->name);
            if ($phaseItem) {
                return ProgressLog::where('phase_item_id', $phaseItem->Phase_Item_Id)
                    ->with('employee:User_Id,name')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(fn($log) => [
                        'employee'  => $log->employee ? $log->employee->name : 'Unknown',
                        'qty_added' => $log->qty_added,
                        'time'      => $log->created_at->format('M d, Y h:i A'),
                    ])->toArray();
            }
        }

        // For non-phase assignments: show this assignment's own log
        return ProgressLog::where('assignment_id', $assignment->Assignment_Id)
            ->with('employee:User_Id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($log) => [
                'employee'  => $log->employee ? $log->employee->name : 'Unknown',
                'qty_added' => $log->qty_added,
                'time'      => $log->created_at->format('M d, Y h:i A'),
            ])->toArray();
    }

    private function adminView()
    {
        $colors = ['bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];

        $employees = User::where('role', 'employee')->get();
        $workers = $employees->map(function ($emp, $index) use ($colors) {
            $activeCount = Assignment::where('employee_id', $emp->User_Id)
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
                'id'          => $emp->User_Id,
                'name'        => $emp->name,
                'initial'     => $emp->initial,
                'color'       => $colors[$index % count($colors)],
                'status'      => $status,
                'statusColor' => $statusColor,
                'active'      => $activeCount,
            ];
        });

        $assignmentsData = [];
        foreach ($employees as $emp) {
            $empAssignments = Assignment::with(['order.items', 'orderItem'])
                ->where('employee_id', $emp->User_Id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('assigned_date', 'desc')
                ->get()
                ->map(function ($a) {
                    $order = $a->order;
                    $assignedItem = $a->orderItem
                        ? "{$a->orderItem->quantity}x {$a->orderItem->name}"
                        : null;
                    return [
                        'id'               => $a->Assignment_Id,
                        'order_id'         => $a->order_number,
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

        $assignedOrderNumbers = Assignment::whereIn('status', ['pending', 'in_progress'])
            ->pluck('order_number')
            ->toArray();

        $availableOrders = Order::whereNotIn('order_number', $assignedOrderNumbers)
            ->whereNotIn('status', ['Ready for Delivery', 'Delivered'])
            ->with(['items', 'phases.items'])
            ->get()
            ->map(function ($order) {
                return [
                    'order_id'         => $order->order_number,
                    'customer'         => $order->customer_name,
                    'customer_contact' => $order->contact_number,
                    'priority'         => strtolower($order->priority ?? 'normal'),
                    'items'            => $order->items->map(fn($i) => $i->quantity . 'x ' . $i->name)->implode(', '),
                    'delivery_address' => $order->delivery_address,
                    'delivery_date'    => $order->delivery_date->format('Y-m-d'),
                    'total_amount'     => (float) $order->total_amount,
                    'notes'            => $order->notes,
                    'order_items'      => $order->items->map(fn($i) => [
                        'id'       => $i->Order_Item_Id,
                        'name'     => $i->name,
                        'quantity' => $i->quantity,
                        'price'    => (float) $i->unit_price,
                    ])->toArray(),
                    'phases'           => $order->phases->sortBy('phase_number')
                        ->filter(fn($p) => $p->status !== 'Delivered')
                        ->map(fn($p) => [
                        'phase_id'      => $p->Phase_Id,
                        'number'        => $p->phase_number,
                        'delivery_date' => $p->delivery_date->format('Y-m-d'),
                        'status'        => $p->status,
                        'items'         => $p->items->map(function ($pi) use ($order) {
                            $orderItem = $order->items->firstWhere('name', $pi->name);
                            return [
                                'id'           => $orderItem ? $orderItem->Order_Item_Id : null,
                                'name'         => $pi->name,
                                'required_qty' => $pi->required_qty,
                                'price'        => $orderItem ? (float) $orderItem->unit_price : 0,
                            ];
                        })->filter(fn($pi) => $pi['id'] !== null)->values()->toArray(),
                    ])->values()->toArray(),
                ];
            })->toArray();

        $activeOrders = Order::whereIn('status', ['In-Progress', 'Pending'])
            ->with(['items', 'phases.items'])
            ->orderBy('delivery_date', 'asc')
            ->get()
            ->map(function ($order) {
                $assignedEmployees = Assignment::where('order_number', $order->order_number)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->with('employee:User_Id,name')
                    ->get()
                    ->pluck('employee.name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
                return [
                    'order_id'         => $order->order_number,
                    'customer'         => $order->customer_name,
                    'customer_contact' => $order->contact_number,
                    'status'           => $order->status,
                    'priority'         => strtolower($order->priority ?? 'normal'),
                    'items'            => $order->items->map(fn($i) => $i->quantity . 'x ' . $i->name)->implode(', '),
                    'delivery_address' => $order->delivery_address,
                    'delivery_date'    => $order->delivery_date->format('Y-m-d'),
                    'total_amount'     => (float) $order->total_amount,
                    'assigned_to'      => $assignedEmployees,
                    'phase_count'      => $order->phases->count(),
                    'progress_items'   => $order->items->map(fn($i) => [
                        'name'          => $i->name,
                        'required_qty'  => $i->quantity,
                        'completed_qty' => $i->completed_qty ?? 0,
                    ])->values()->toArray(),
                    'phases_progress'  => $order->phases->sortBy('phase_number')->map(fn($p) => [
                        'number'        => $p->phase_number,
                        'status'        => $p->status,
                        'delivery_date' => $p->delivery_date->format('Y-m-d'),
                        'items'         => $p->items->map(fn($pi) => [
                            'name'          => $pi->name,
                            'required_qty'  => $pi->required_qty,
                            'completed_qty' => $pi->completed_qty,
                        ])->values()->toArray(),
                    ])->values()->toArray(),
                ];
            })->toArray();

        return view('pages.assignments', compact('workers', 'assignmentsData', 'availableOrders', 'activeOrders'));
    }

    public function store(Request $request)
    {
        // ── Per-item assignment (multiple products → multiple employees) ──
        if ($request->has('item_assignments')) {
            $validated = $request->validate([
                'order_id'                         => 'required|string|exists:orders,order_number',
                'priority'                         => 'required|in:normal,high,urgent',
                'notes'                            => 'nullable|string',
                'item_assignments'                 => 'required|array|min:1',
                'item_assignments.*.order_item_id' => 'required|exists:order_items,Order_Item_Id',
                'item_assignments.*.employee_id'   => 'required|exists:users,User_Id',
                'item_assignments.*.phase_id'      => 'nullable|exists:order_phases,Phase_Id',
            ]);

            $order = Order::where('order_number', $validated['order_id'])->first();

            DB::beginTransaction();
            try {
                $employeesById = [];
                $createdAssignments = [];
                $stockedOutItemIds = []; // track order_item_ids already stocked out

                foreach ($validated['item_assignments'] as $ia) {
                    $employee = User::find($ia['employee_id']);
                    if (!$employee) continue;

                    $assignment = Assignment::create([
                        'order_number'  => $validated['order_id'],
                        'order_item_id' => $ia['order_item_id'],
                        'phase_id'      => $ia['phase_id'] ?? null,
                        'employee_id'   => $ia['employee_id'],
                        'priority'      => $validated['priority'],
                        'status'        => 'pending',
                        'notes'         => $validated['notes'] ?? null,
                        'assigned_by'   => auth()->id(),
                        'assigned_date' => now()->toDateString(),
                    ]);

                    $createdAssignments[] = $assignment;
                    $employeesById[$employee->User_Id] = $employee;

                    $orderItem = OrderItem::find($ia['order_item_id']);
                    if ($orderItem && !in_array($orderItem->Order_Item_Id, $stockedOutItemIds)) {
                        $inventoryItem = InventoryItem::find($orderItem->product_id);
                        if ($inventoryItem) {
                            $previousStock = $inventoryItem->stock;
                            $newStock      = max(0, $previousStock - $orderItem->quantity);

                            $inventoryItem->update([
                                'stock'  => $newStock,
                                'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
                            ]);

                            StockOut::create([
                                'product_id'       => $inventoryItem->Product_Id,
                                'quantity'         => $orderItem->quantity,
                                'previous_stock'   => $previousStock,
                                'new_stock'        => $newStock,
                                'reference_number' => 'SO-' . now()->format('YmdHis') . rand(10, 99),
                                'reason'           => 'Order Assignment',
                                'notes'            => "Auto stock out for order {$validated['order_id']} – {$orderItem->name} assigned to {$employee->name}",
                                'created_by'       => auth()->id(),
                                'created_at'       => now(),
                            ]);

                            $stockedOutItemIds[] = $orderItem->Order_Item_Id;
                        }
                    }
                }

                if ($order) {
                    $assignedNames = count($employeesById) > 1
                        ? 'Multiple'
                        : (count($employeesById) === 1 ? array_values($employeesById)[0]->name : null);

                    $order->update([
                        'assigned' => $assignedNames,
                        'status'   => 'In-Progress',
                    ]);
                }

                foreach ($employeesById as $empId => $employee) {
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
            'employee_id' => 'required|exists:users,User_Id',
            'priority'    => 'required|in:normal,high,urgent',
            'notes'       => 'nullable|string',
        ]);

        $employee = User::findOrFail($validated['employee_id']);

        $assignment = Assignment::create([
            'order_number'  => $validated['order_id'],
            'order_item_id' => null,
            'employee_id'   => $validated['employee_id'],
            'priority'      => $validated['priority'],
            'status'        => 'pending',
            'notes'         => $validated['notes'] ?? null,
            'assigned_by'   => auth()->id(),
            'assigned_date' => now()->toDateString(),
        ]);

        $order = Order::where('order_number', $validated['order_id'])->first();
        if ($order) {
            $order->update([
                'assigned' => $employee->name,
                'status'   => 'In-Progress',
            ]);

            foreach ($order->items as $orderItem) {
                $inventoryItem = InventoryItem::find($orderItem->product_id);
                if ($inventoryItem) {
                    $previousStock = $inventoryItem->stock;
                    $newStock = max(0, $previousStock - $orderItem->quantity);

                    $inventoryItem->update([
                        'stock'  => $newStock,
                        'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
                    ]);

                    StockOut::create([
                        'product_id'       => $inventoryItem->Product_Id,
                        'quantity'         => $orderItem->quantity,
                        'previous_stock'   => $previousStock,
                        'new_stock'        => $newStock,
                        'reference_number' => 'SO-' . now()->format('YmdHis') . rand(10, 99),
                        'reason'           => 'Order Assignment',
                        'notes'            => "Auto stock out for order {$validated['order_id']} assigned to {$employee->name}",
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                    ]);
                }
            }
        }

        Notification::send(
            $employee->User_Id,
            'work_assigned',
            'New Work Assigned',
            "You have been assigned to work on order {$validated['order_id']}. Priority: {$validated['priority']}.",
            ['order_id' => $validated['order_id'], 'assignment_id' => $assignment->Assignment_Id]
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
        $assignment->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }

    public function updateStatus(Request $request, Assignment $assignment)
    {
        $user = auth()->user();

        if ($assignment->employee_id !== $user->User_Id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $assignment->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    public function updateProgress(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'assignment_id'   => 'required|exists:assignments,Assignment_Id',
            'items'           => 'required|array',
            'items.*.id'      => 'required|exists:order_items,Order_Item_Id',
            'items.*.add_qty' => 'required|integer|min:1',
        ]);

        $assignment = Assignment::findOrFail($validated['assignment_id']);

        if ($assignment->employee_id !== $user->User_Id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $order = Order::where('order_number', $assignment->order_number)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Phase-specific assignment: update OrderPhaseItem.completed_qty, not OrderItem
        if ($assignment->phase_id) {
            foreach ($validated['items'] as $itemData) {
                $orderItem = OrderItem::where('Order_Item_Id', $itemData['id'])
                    ->where('order_id', $order->Order_Id)
                    ->first();
                if (!$orderItem) continue;

                $phaseItem = OrderPhaseItem::where('phase_id', $assignment->phase_id)
                    ->where('name', $orderItem->name)
                    ->first();
                if ($phaseItem) {
                    $prevCompleted = $phaseItem->completed_qty;
                    $newTotal      = min($prevCompleted + $itemData['add_qty'], $phaseItem->required_qty);
                    $actualAdd     = $newTotal - $prevCompleted;
                    if ($actualAdd > 0) {
                        $phaseItem->update(['completed_qty' => $newTotal]);
                        ProgressLog::create([
                            'assignment_id' => $assignment->Assignment_Id,
                            'phase_item_id' => $phaseItem->Phase_Item_Id,
                            'employee_id'   => $user->User_Id,
                            'qty_added'     => $actualAdd,
                        ]);
                    }
                }
            }

            $phase   = $assignment->phase()->with('items')->first();
            $allDone = $phase && $phase->items->every(fn($i) => $i->fresh()->completed_qty >= $i->required_qty);

            if ($allDone) {
                // Complete ALL assignments for this phase (multiple employees may be assigned)
                Assignment::where('order_number', $order->order_number)
                    ->where('phase_id', $assignment->phase_id)
                    ->whereNotIn('status', ['cancelled'])
                    ->update(['status' => 'completed']);

                if ($phase->status !== 'Completed') {
                    $phase->update(['status' => 'Completed']);
                }

                // Mark order as Ready for Delivery so it shows in Dispatch
                $order->update(['status' => 'Ready for Delivery']);

                $managerIds = User::where('role', 'admin')->orWhere('role', 'super_admin')->pluck('User_Id')->toArray();
                Notification::sendToMany(
                    $managerIds,
                    'phase_completed',
                    'Phase Ready for Delivery',
                    "Phase {$phase->phase_number} of order {$order->order_number} is complete and ready for delivery.",
                    ['order_id' => $order->order_number]
                );
            } else {
                $assignment->update(['status' => 'in_progress']);
            }

            return response()->json([
                'success'       => true,
                'all_completed' => $allDone,
                'order_status'  => $order->fresh()->status,
            ]);
        }

        // Non-phase assignment: update OrderItem.completed_qty directly
        foreach ($validated['items'] as $itemData) {
            $orderItem = OrderItem::where('Order_Item_Id', $itemData['id'])
                ->where('order_id', $order->Order_Id)
                ->first();

            if ($orderItem) {
                $prevCompleted = $orderItem->completed_qty;
                $newTotal      = min($prevCompleted + $itemData['add_qty'], $orderItem->quantity);
                $actualAdd     = $newTotal - $prevCompleted;
                if ($actualAdd > 0) {
                    $orderItem->update(['completed_qty' => $newTotal]);
                    ProgressLog::create([
                        'assignment_id' => $assignment->Assignment_Id,
                        'order_item_id' => $orderItem->Order_Item_Id,
                        'employee_id'   => $user->User_Id,
                        'qty_added'     => $actualAdd,
                    ]);
                }
            }
        }

        $order->refresh();
        $allDone = $order->items->every(fn($i) => $i->completed_qty >= $i->quantity);

        if ($allDone) {
            $order->update(['status' => 'Ready for Delivery']);

            Assignment::where('order_number', $order->order_number)
                ->whereNotIn('status', ['cancelled'])
                ->update(['status' => 'completed']);

            $managerIds = User::where('role', 'admin')->pluck('User_Id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'order_ready',
                'Order Ready for Delivery',
                "Order {$order->order_number} is ready for delivery. All items have been completed by {$user->name}.",
                ['order_id' => $order->order_number]
            );
        } else {
            if ($assignment->order_item_id) {
                $assignedItem = $order->items->firstWhere('Order_Item_Id', $assignment->order_item_id);
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