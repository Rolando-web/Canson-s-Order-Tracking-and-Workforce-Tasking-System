<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPhaseItem;
use App\Models\OrderPhase;
use App\Models\ProgressLog;
use App\Models\User;
use App\Models\Product;
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
        $rawAssignments = Assignment::with(['order.items', 'orderItem', 'phase.items'])
            ->where('employee_id', $user->User_Id)
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'completed', 'cancelled')")
            ->orderBy('assigned_date', 'desc')
            ->get();

        // Group assignments by order + phase
        $grouped = [];
        foreach ($rawAssignments as $a) {
            $key = $a->order_number . '|' . ($a->phase_id ?? 'none');
            $order = $a->order;

            if (!isset($grouped[$key])) {
                $phase = $a->phase;
                $grouped[$key] = [
                    'ids'              => [],
                    'order_id'         => $a->order_number,
                    'phase_id'         => $a->phase_id,
                    'phase_number'     => $phase ? $phase->phase_number : null,
                    'customer'         => $order ? $order->customer_name : 'N/A',
                    'customer_contact' => $order ? $order->contact_number : '',
                    'delivery_address' => $order ? $order->delivery_address : '',
                    'delivery_date'    => $phase && $phase->delivery_date
                        ? $phase->delivery_date->format('Y-m-d')
                        : ($order ? $order->delivery_date->format('Y-m-d') : ''),
                    'total_amount'     => $order ? $order->total_amount : 0,
                    'priority'         => $a->priority,
                    'status'           => $a->status,
                    'order_status'     => $order ? $order->status : 'Pending',
                    'notes'            => $a->notes,
                    'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                    'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                    'order_items'      => [],
                    'progress_history' => [],
                ];
            }

            $grouped[$key]['ids'][] = $a->Assignment_Id;

            // Use worst status in the group
            $statusPriority = ['in_progress' => 0, 'pending' => 1, 'completed' => 2, 'cancelled' => 3];
            $currentPri = $statusPriority[$grouped[$key]['status']] ?? 9;
            $thisPri = $statusPriority[$a->status] ?? 9;
            if ($thisPri < $currentPri) {
                $grouped[$key]['status'] = $a->status;
            }

            // Build order item entry
            if ($a->order_item_id && $a->orderItem) {
                $assignedItem = $a->orderItem;
                $displayQty = $assignedItem->quantity;
                $phaseCompleted = $assignedItem->completed_qty ?? 0;
                if ($a->phase_id && $a->phase) {
                    $phaseItem = $a->phase->items->firstWhere('name', $assignedItem->name);
                    if ($phaseItem) {
                        $displayQty = $phaseItem->required_qty;
                        $phaseCompleted = $phaseItem->completed_qty;
                    }
                }

                // Avoid duplicates (same item in same group)
                $exists = collect($grouped[$key]['order_items'])->firstWhere('id', $assignedItem->Order_Item_Id);
                if (!$exists) {
                    $grouped[$key]['order_items'][] = [
                        'id'            => $assignedItem->Order_Item_Id,
                        'name'          => $assignedItem->name,
                        'quantity'      => $displayQty,
                        'completed_qty' => $phaseCompleted,
                        'remaining'     => $displayQty - $phaseCompleted,
                    ];
                }
            }

            // Merge progress history
            $history = $this->loadProgressHistory($a);
            $grouped[$key]['progress_history'] = array_merge($grouped[$key]['progress_history'], $history);

            // Auto-complete logic
            if ($a->status === 'in_progress') {
                $orderItems = $grouped[$key]['order_items'];
                $allDone = !empty($orderItems) && collect($orderItems)->every(fn($i) => $i['remaining'] <= 0);
                if ($allDone) {
                    $a->update(['status' => 'completed']);
                    if ($order) {
                        $stillOpen = Assignment::where('order_number', $order->order_number)
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->count();
                        if ($stillOpen === 0 && $order->status === 'In-Progress') {
                            $remainingPhases = $order->phases()
                                ->whereNotIn('status', ['Completed', 'Delivered'])
                                ->count();
                            if ($remainingPhases === 0) {
                                $order->update(['status' => 'Ready for Delivery']);
                            }
                        }
                    }
                }
            }
        }

        // Sort history by time desc and pick first assignment ID for form
        // Also load all phases progress for the order so employee sees full picture
        $myAssignments = collect($grouped)->map(function ($group) {
            usort($group['progress_history'], fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));
            $group['id'] = $group['ids'][0]; // first assignment ID for the form
            $group['items'] = collect($group['order_items'])->map(fn($i) => $i['quantity'] . ' ' . $i['name'])->implode(', ');

            // Load all phases of this order for the overview
            $order = Order::where('order_number', $group['order_id'])->with('phases.items')->first();
            $group['all_phases'] = [];
            if ($order && $order->phases->isNotEmpty()) {
                $group['all_phases'] = $order->phases->sortBy('phase_number')->map(function ($p) {
                    $totalReq = $p->items->sum('required_qty');
                    $totalDone = $p->items->sum('completed_qty');
                    return [
                        'number'        => $p->phase_number,
                        'status'        => $p->status,
                        'delivery_date' => $p->delivery_date->format('Y-m-d'),
                        'total_req'     => $totalReq,
                        'total_done'    => $totalDone,
                        'pct'           => $totalReq > 0 ? round(($totalDone / $totalReq) * 100) : 0,
                    ];
                })->values()->toArray();
            }

            return $group;
        })->values()->toArray();

        $newAssignmentCount = Assignment::where('employee_id', $user->User_Id)
            ->where('status', 'pending')
            ->count();

        return view('pages.assignments-employee', compact('myAssignments', 'newAssignmentCount'));
    }

    private function loadProgressHistory($assignment): array
    {
        if ($assignment->phase_id && $assignment->phase && $assignment->orderItem) {
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

        return [];
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
            $empAssignments = Assignment::with(['order.items', 'orderItem', 'phase.items'])
                ->where('employee_id', $emp->User_Id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('assigned_date', 'desc')
                ->get();

            // Group assignments by order+phase for cleaner display
            $grouped = [];
            foreach ($empAssignments as $a) {
                $key = $a->order_number . '|' . ($a->phase_id ?? 'none');
                if (!isset($grouped[$key])) {
                    $order = $a->order;
                    $phase = $a->phase;
                    $grouped[$key] = [
                        'order_id'         => $a->order_number,
                        'phase_id'         => $a->phase_id,
                        'phase_number'     => $phase ? $phase->phase_number : null,
                        'customer'         => $order ? $order->customer_name : 'N/A',
                        'customer_contact' => $order ? $order->contact_number : '',
                        'delivery_address' => $order ? $order->delivery_address : '',
                        'delivery_date'    => $phase && $phase->delivery_date
                            ? $phase->delivery_date->format('Y-m-d')
                            : ($order ? $order->delivery_date->format('Y-m-d') : ''),
                        'total_amount'     => $order ? $order->total_amount : 0,
                        'priority'         => $a->priority,
                        'status'           => $a->status,
                        'order_status'     => $order ? $order->status : 'Pending',
                        'notes'            => $a->notes,
                        'assigned_date'    => $a->assigned_date->format('Y-m-d'),
                        'assigned_by'      => $a->assignedByUser ? $a->assignedByUser->name : 'System',
                        'items_list'       => [],
                    ];
                }
                // Collect assigned items for this group
                if ($a->orderItem) {
                    $phaseItem = $a->phase ? $a->phase->items->firstWhere('name', $a->orderItem->name) : null;
                    $grouped[$key]['items_list'][] = [
                        'name'     => $a->orderItem->name,
                        'quantity' => $phaseItem ? $phaseItem->required_qty : $a->orderItem->quantity,
                    ];
                }
            }

            $assignmentsData[$emp->name] = array_values($grouped);
        }

        // Phase-level assignment tracking
        $assignedPhaseIds = Assignment::whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('phase_id')
            ->pluck('phase_id')
            ->unique()
            ->toArray();

        // Orders with ANY phased assignment are fully assigned (all phases get same employee)
        $fullyAssignedPhasedOrders = Assignment::whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('phase_id')
            ->pluck('order_number')
            ->unique()
            ->toArray();

        $availableOrders = Order::whereNotIn('status', ['Delivered'])
            ->with(['items', 'phases.items'])
            ->get()
            ->filter(function ($order) use ($fullyAssignedPhasedOrders) {
                // Hide if any phase is assigned (all phases assigned together)
                if (in_array($order->order_number, $fullyAssignedPhasedOrders)) {
                    return false;
                }

                // Hide if all phases are Completed/Delivered
                $hasAssignablePhase = $order->phases
                    ->filter(fn($p) => !in_array($p->status, ['Completed', 'Delivered']))
                    ->isNotEmpty();

                return $hasAssignablePhase;
            })
            ->map(function ($order) use ($assignedPhaseIds) {
                // Build phase status info for order card badges
                $allPhasesStatus = $order->phases->sortBy('phase_number')->map(function ($p) use ($assignedPhaseIds) {
                    $isAssigned  = in_array($p->Phase_Id, $assignedPhaseIds);
                    $isCompleted = $p->status === 'Completed';
                    $isDelivered = $p->status === 'Delivered';

                    if ($isDelivered) {
                        $statusLabel = 'Delivered';
                    } elseif ($isCompleted) {
                        $statusLabel = 'Completed';
                    } elseif ($isAssigned) {
                        $statusLabel = 'Assigned';
                    } else {
                        $statusLabel = 'Pending';
                    }

                    return [
                        'phase_id'      => $p->Phase_Id,
                        'number'        => $p->phase_number,
                        'delivery_date' => $p->delivery_date->format('Y-m-d'),
                        'status'        => $p->status,
                        'status_label'  => $statusLabel,
                    ];
                })->values()->toArray();

                return [
                    'order_id'          => $order->order_number,
                    'customer'          => $order->customer_name,
                    'customer_contact'  => $order->contact_number,
                    'priority'          => strtolower($order->priority ?? 'normal'),
                    'items'             => $order->items->map(fn($i) => $i->quantity . 'x ' . $i->name)->implode(', '),
                    'delivery_address'  => $order->delivery_address,
                    'delivery_date'     => $order->delivery_date->format('Y-m-d'),
                    'total_amount'      => (float) $order->total_amount,
                    'notes'             => $order->notes,
                    'order_items'       => $order->items->map(fn($i) => [
                        'id'       => $i->Order_Item_Id,
                        'name'     => $i->name,
                        'quantity' => $i->quantity,
                        'price'    => (float) $i->unit_price,
                    ])->toArray(),
                    'all_phases_status' => $allPhasesStatus,
                    // All unfinished phases for the assignment modal (all assigned together)
                    'phases'            => $order->phases->sortBy('phase_number')
                        ->filter(fn($p) => !in_array($p->status, ['Completed', 'Delivered']))
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
            })
            ->values()
            ->toArray();

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
                        'phase_id'      => $p->Phase_Id,
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

                // Collect all phases that need assignment (for phased orders, auto-assign all phases to same employees)
                $allPhases = $order->phases()
                    ->whereNotIn('status', ['Completed', 'Delivered'])
                    ->whereNotIn('Phase_Id', Assignment::whereIn('status', ['pending', 'in_progress'])->whereNotNull('phase_id')->pluck('phase_id')->toArray())
                    ->orderBy('phase_number')
                    ->get();

                // Build the employee-item mapping from the submitted assignments (based on item NAME, not ID)
                $itemEmployeeMap = [];
                foreach ($validated['item_assignments'] as $ia) {
                    $orderItem = OrderItem::find($ia['order_item_id']);
                    if (!$orderItem) continue;
                    $itemEmployeeMap[$orderItem->name][] = $ia['employee_id'];
                }

                // Create assignments for ALL remaining phases using the same employee mapping
                if ($allPhases->isNotEmpty()) {
                    foreach ($allPhases as $phase) {
                        foreach ($phase->items as $phaseItem) {
                            $orderItem = $order->items->firstWhere('name', $phaseItem->name);
                            if (!$orderItem) continue;

                            $empIds = $itemEmployeeMap[$phaseItem->name] ?? [];
                            foreach ($empIds as $empId) {
                                $employee = User::find($empId);
                                if (!$employee) continue;

                                $assignment = Assignment::create([
                                    'order_number'  => $validated['order_id'],
                                    'order_item_id' => $orderItem->Order_Item_Id,
                                    'phase_id'      => $phase->Phase_Id,
                                    'employee_id'   => $empId,
                                    'priority'      => $validated['priority'],
                                    'status'        => 'pending',
                                    'notes'         => $validated['notes'] ?? null,
                                    'assigned_by'   => auth()->id(),
                                    'assigned_date' => now()->toDateString(),
                                ]);

                                $createdAssignments[] = $assignment;
                                $employeesById[$employee->User_Id] = $employee;
                            }
                        }
                    }
                }

                // Stock out (once per order item, not per phase)
                foreach ($validated['item_assignments'] as $ia) {
                    $orderItem = OrderItem::find($ia['order_item_id']);
                    if ($orderItem && !in_array($orderItem->Order_Item_Id, $stockedOutItemIds)) {
                        $employee = User::find($ia['employee_id']);
                        $inventoryItem = Product::find($orderItem->product_id);
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
                                'notes'            => "Auto stock out for order {$validated['order_id']} – {$orderItem->name}" . ($employee ? " assigned to {$employee->name}" : ''),
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

        // All orders use item_assignments with phases
        return response()->json(['success' => false, 'message' => 'Missing item_assignments'], 422);
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

                // Check if ALL phases of this order are now Completed or Delivered
                $remainingPendingPhases = OrderPhase::where('order_id', $order->Order_Id)
                    ->whereNotIn('status', ['Completed', 'Delivered'])
                    ->count();

                if ($remainingPendingPhases === 0) {
                    $order->update(['status' => 'Ready for Delivery']);
                } else {
                    // Other phases still need work/assignment -- keep order In-Progress
                    if ($order->status !== 'In-Progress') {
                        $order->update(['status' => 'In-Progress']);
                    }
                }

                $managerIds = User::where('role', 'admin')->orWhere('role', 'super_admin')->pluck('User_Id')->toArray();
                Notification::sendToMany(
                    $managerIds,
                    'phase_completed',
                    'Phase Ready for Delivery',
                    "Phase {$phase->phase_number} of order {$order->order_number} is complete and ready for delivery."
                        . ($remainingPendingPhases > 0 ? " {$remainingPendingPhases} phase(s) still pending." : " All phases complete!"),
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

        return response()->json(['success' => false, 'message' => 'No phase found for this assignment'], 422);
    }

    public function getPhaseNotes(OrderPhase $phase)
    {
        return response()->json([
            'success' => true,
            'notes'   => $phase->notes ?? '',
        ]);
    }

    public function updatePhaseNotes(Request $request, OrderPhase $phase)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $phase->update(['notes' => $validated['notes']]);

        return response()->json([
            'success' => true,
            'notes'   => $phase->notes,
        ]);
    }
}