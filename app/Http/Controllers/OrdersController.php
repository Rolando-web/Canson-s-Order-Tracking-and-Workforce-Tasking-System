<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderPhase;
use App\Models\OrderPhaseItem;
use App\Models\ReturnItem;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index()
    {
        $inventoryItems = Product::select('Product_Id', 'name', 'item_code', 'stock', 'unit', 'unit_price')
            ->orderBy('name')
            ->get();

        $orders = Order::with(['phases.items'])
            ->whereNotIn('status', ['Delivered', 'Ready for Delivery'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $priorityColors = [
                    'Normal' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'High'   => 'bg-amber-50 text-amber-600 border-amber-200',
                    'Urgent' => 'bg-red-50 text-red-600 border-red-200',
                ];

                // Collect all phase items (deduplicated by name for display)
                $allPhaseItems = $order->phases->flatMap->items;
                $uniqueItemNames = $allPhaseItems->pluck('name')->unique();
                $totalQty = $allPhaseItems->sum('base_qty');

                return [
                    'id'            => $order->order_number,
                    'db_id'         => $order->Order_Id,
                    'customer'      => $order->customer_name,
                    'contact'       => $order->contact_number,
                    'address'       => $order->delivery_address,
                    'items'         => $uniqueItemNames->implode(', '),
                    'total_qty'     => $totalQty,
                    'delivery_date' => $order->delivery_date->format('Y-m-d'),
                    'total'         => $order->total_amount,
                    'status'        => $order->status,
                    'assigned'      => $order->assigned,
                    'initial'       => $order->assigned ? strtoupper(substr($order->assigned, 0, 1)) : null,
                    'priority'      => $order->priority,
                    'priorityColor' => $priorityColors[$order->priority] ?? $priorityColors['Normal'],
                    'notes'         => $order->notes,
                    'phase_count'   => $order->phases->count(),
                    'order_items'   => $order->phases->first()?->items->map(fn($i) => [
                        'name'          => $i->name,
                        'qty'           => $i->base_qty,
                        'price'         => $i->unit_price,
                        'subtotal'      => $i->subtotal,
                        'completed_qty' => $i->completed_qty ?? 0,
                    ])->toArray() ?? [],
                    'phases'        => $order->phases->sortBy('phase_number')->map(fn($p) => [
                        'number'        => $p->phase_number,
                        'delivery_date' => $p->delivery_date->format('Y-m-d'),
                        'status'        => $p->status,
                        'items'         => $p->items->map(fn($pi) => [
                            'name'          => $pi->name,
                            'required_qty'  => $pi->required_qty,
                            'completed_qty' => $pi->completed_qty,
                        ])->toArray(),
                    ])->values()->toArray(),
                ];
            });

        return view('pages.orders', compact('inventoryItems', 'orders'));
    }

    public function customerSuggestions(Request $request)
    {
        $q = $request->query('q', '');

        $customers = Order::select('customer_name', 'contact_number', 'delivery_address')
            ->when($q, fn($query) => $query->where('customer_name', 'LIKE', "%{$q}%"))
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('customer_name')
            ->map(fn($o) => [
                'name'    => $o->customer_name,
                'contact' => $o->contact_number,
                'address' => $o->delivery_address,
            ])
            ->values();

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'            => 'required|string|max:100',
            'contact_number'           => 'required|string|max:11',
            'delivery_address'         => 'required|string',
            'delivery_date'            => 'required|date',
            'priority'                 => 'required|in:Normal,High,Urgent',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.name'             => 'required|string',
            'items.*.qty'              => 'required|integer|min:1',
            'items.*.price'            => 'required|numeric|min:0',
            'cover_claim_ids'          => 'nullable|array',
            'cover_claim_ids.*'        => 'integer|exists:returns,Return_Id',
            // Phases (optional)
            'phases'                   => 'nullable|array',
            'phases.*.delivery_date'   => 'required_with:phases|date',
            'phases.*.items'           => 'required_with:phases|array',
            'phases.*.items.*.name'    => 'required|string',
            'phases.*.items.*.qty'     => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['qty'] * $item['price'];
            }

            foreach ($validated['items'] as $item) {
                $inv = Product::where('name', $item['name'])->first();
                if ($inv && $item['qty'] > $inv->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => "\"{$item['name']}\" only has {$inv->stock} in stock.",
                    ], 422);
                }
            }

            $order = Order::create([
                'order_number'     => Order::generateOrderId(),
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

            // Build a price lookup from the submitted items
            $itemPrices = [];
            foreach ($validated['items'] as $item) {
                $itemPrices[$item['name']] = $item['price'];
            }

            // Cover damage claims
            if (!empty($validated['cover_claim_ids'])) {
                ReturnItem::whereIn('Return_Id', $validated['cover_claim_ids'])
                    ->where('status', 'Pending')
                    ->update([
                        'status'           => 'Covered',
                        'covered_by_order' => $order->order_number,
                    ]);
            }

            // Always create phases — if none provided, auto-create phases
            // If any item has qty > 1000, auto-split into Phase 1 + Phase 2 (1 week apart)
            if (!empty($validated['phases'])) {
                $phasesData = $validated['phases'];
            } else {
                $hasLargeQty = collect($validated['items'])->contains(fn($item) => $item['qty'] >= 1000);

                if ($hasLargeQty) {
                    // Split each item: Phase 1 gets half (ceil), Phase 2 gets the rest
                    $phase1Items = [];
                    $phase2Items = [];
                    foreach ($validated['items'] as $item) {
                        $phase1Qty = (int) ceil($item['qty'] / 2);
                        $phase2Qty = $item['qty'] - $phase1Qty;
                        $phase1Items[] = ['name' => $item['name'], 'qty' => $phase1Qty];
                        if ($phase2Qty > 0) {
                            $phase2Items[] = ['name' => $item['name'], 'qty' => $phase2Qty];
                        }
                    }

                    $phase1Date = $validated['delivery_date'];
                    $phase2Date = \Carbon\Carbon::parse($validated['delivery_date'])->addWeek()->format('Y-m-d');

                    $phasesData = [
                        ['delivery_date' => $phase1Date, 'notes' => null, 'items' => $phase1Items],
                        ['delivery_date' => $phase2Date, 'notes' => null, 'items' => $phase2Items],
                    ];
                } else {
                    $phasesData = [
                        [
                            'delivery_date' => $validated['delivery_date'],
                            'notes'         => null,
                            'items'         => array_map(fn($item) => [
                                'name' => $item['name'],
                                'qty'  => $item['qty'],
                            ], $validated['items']),
                        ],
                    ];
                }
            }

            foreach ($phasesData as $phaseIndex => $phaseData) {
                $phaseNumber = $phaseIndex + 1;

                $phase = OrderPhase::create([
                    'order_id'      => $order->Order_Id,
                    'phase_number'  => $phaseNumber,
                    'delivery_date' => $phaseData['delivery_date'],
                    'status'        => 'Pending',
                    'damage_qty'    => 0,
                    'notes'         => $phaseData['notes'] ?? null,
                ]);

                foreach ($phaseData['items'] as $phaseItem) {
                    if (($phaseItem['qty'] ?? 0) <= 0) continue;

                    $inv = Product::where('name', $phaseItem['name'])->first();
                    $price = $itemPrices[$phaseItem['name']] ?? 0;

                    OrderPhaseItem::create([
                        'phase_id'     => $phase->Phase_Id,
                        'product_id'   => $inv ? $inv->Product_Id : null,
                        'name'         => $phaseItem['name'],
                        'base_qty'     => $phaseItem['qty'],
                        'damage_carry' => 0,
                        'required_qty' => $phaseItem['qty'],
                        'completed_qty'=> 0,
                        'unit_price'   => $price,
                        'subtotal'     => $phaseItem['qty'] * $price,
                    ]);
                }
            }

            DB::commit();

            $managerIds = User::where('role', 'admin')->pluck('User_Id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'new_order',
                'New Order Created',
                "Order {$order->order_number} for {$order->customer_name} (₱" . number_format($totalAmount, 2) . ") needs to be assigned.",
                ['order_id' => $order->order_number]
            );

            return response()->json(['success' => true, 'order' => $order->load('phases.items')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status'   => 'sometimes|in:Pending,In-Progress,Completed,Ready for Delivery,Delivered',
            'assigned' => 'sometimes|nullable|string|max:100',
            'priority' => 'sometimes|in:Normal,High,Urgent',
            'notes'    => 'sometimes|nullable|string',
        ]);
        $order->update($validated);
        return response()->json(['success' => true, 'order' => $order]);
    }

    public function destroy(Request $request, Order $order)
    {
        $order->delete();
        return response()->json(['success' => true]);
    }
}