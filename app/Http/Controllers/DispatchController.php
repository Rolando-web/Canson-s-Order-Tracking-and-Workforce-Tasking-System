<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Order;
use App\Models\OrderPhase;
use App\Models\OrderPhaseItem;
use App\Models\Notification;
use App\Models\User;
use App\Models\ReturnItem;
use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index()
    {
        $rawOrders = Order::with(['items', 'phases.items'])
            ->where(function ($query) {
                $query->whereIn('status', ['Ready for Delivery', 'Delivered'])
                      ->orWhere(function ($q) {
                          // In-Progress orders with at least one Completed or Delivered phase
                          $q->where('status', 'In-Progress')
                            ->whereHas('phases', fn($ph) => $ph->whereIn('status', ['Completed', 'Delivered']));
                      });
            })
            ->orderBy('delivery_date', 'asc')
            ->get();

        $orders = collect();

        foreach ($rawOrders as $order) {
            $coveredClaims  = ReturnItem::where('covered_by_order', $order->order_number)->get();
            $coveredItemIds = $coveredClaims->pluck('product_id')->toArray();
            $hasPhases      = $order->phases->count() > 0;

            if ($hasPhases) {
                // For phased orders, create a separate entry per phase that is Completed or Delivered
                foreach ($order->phases->sortBy('phase_number') as $phase) {
                    if (!in_array($phase->status, ['Completed', 'Delivered'])) {
                        continue;
                    }

                    $phaseItemNames = $phase->items->map(fn($i) => $i->required_qty . ' ' . $i->name)->implode(', ');

                    // Calculate phase subtotal from matching order items
                    $phaseSubtotal = 0;
                    $phaseItemDetails = $phase->items->map(function ($pi) use ($order, $coveredItemIds, &$phaseSubtotal) {
                        $orderItem = $order->items->firstWhere('name', $pi->name);
                        $unitPrice = $orderItem ? (float) $orderItem->unit_price : 0;
                        $isCover   = $orderItem && in_array($orderItem->product_id, $coveredItemIds) && $unitPrice === 0.0;
                        $phaseSubtotal += $pi->required_qty * $unitPrice;
                        return [
                            'product_id' => $orderItem->product_id ?? null,
                            'name'       => $pi->name,
                            'quantity'   => $pi->required_qty,
                            'unit_price' => $unitPrice,
                            'is_cover'   => $isCover,
                        ];
                    })->values();

                    $displayStatus = $phase->status === 'Delivered' ? 'Delivered' : 'Ready for Delivery';

                    $orders->push([
                        'id'              => $order->Order_Id,
                        'order_id'        => $order->order_number,
                        'customer'        => $order->customer_name,
                        'items'           => $phaseItemNames,
                        'item_details'    => $phaseItemDetails,
                        'has_cover_items' => $coveredClaims->count() > 0,
                        'address'         => $order->delivery_address,
                        'contact'         => $order->contact_number,
                        'total_amount'    => $phaseSubtotal,
                        'status'          => $displayStatus,
                        'delivery_date'   => $phase->delivery_date->format('Y-m-d'),
                        'delivered_at'    => $phase->updated_at->format('M d, Y h:i A'),
                        'assigned'        => $order->assigned,
                        'phase_number'    => $phase->phase_number,
                        'total_phases'    => $order->phases->count(),
                        'current_phase'   => $displayStatus === 'Ready for Delivery' ? [
                            'id'            => $phase->Phase_Id,
                            'phase_number'  => $phase->phase_number,
                            'delivery_date' => $phase->delivery_date->format('M d, Y'),
                            'items'         => $phase->items->map(fn($i) => [
                                'id'           => $i->Phase_Item_Id,
                                'name'         => $i->name,
                                'required_qty' => $i->required_qty,
                            ])->toArray(),
                        ] : null,
                        'phases'          => $order->phases->sortBy('phase_number')->map(fn($p) => [
                            'id'            => $p->Phase_Id,
                            'phase_number'  => $p->phase_number,
                            'delivery_date' => $p->delivery_date->format('M d, Y'),
                            'status'        => $p->status,
                            'damage_qty'    => $p->damage_qty,
                        ])->values()->toArray(),
                        // Unique key for modal IDs (order_id + phase)
                        'dispatch_key'    => $order->Order_Id . '-p' . $phase->phase_number,
                    ]);
                }
            }
        }

        // Sort: Ready for Delivery first, then Delivered
        $orders = $orders->sortBy(function ($o) {
            return $o['status'] === 'Ready for Delivery' ? 0 : 1;
        })->values();

        $readyCount     = $orders->where('status', 'Ready for Delivery')->count();
        $deliveredCount = $orders->where('status', 'Delivered')->count();

        return view('pages.dispatch', compact('orders', 'readyCount', 'deliveredCount'));
    }

    public function deliver(Request $request)
    {
        $validated = $request->validate([
            'order_id'            => 'required|integer|exists:orders,Order_Id',
            'damages'             => 'nullable|array',
            'damages.*.item_id'   => 'required_with:damages|integer|exists:products,Product_Id',
            'damages.*.item_name' => 'required_with:damages|string|max:255',
            'damages.*.quantity'  => 'required_with:damages|integer|min:1',
            'damages.*.reason'    => 'required_with:damages|string|max:255',
            'phase_id'            => 'required|integer|exists:order_phases,Phase_Id',
            'phase_damages'       => 'nullable|array',
            'phase_damages.*.name'       => 'required_with:phase_damages|string',
            'phase_damages.*.damage_qty' => 'required_with:phase_damages|integer|min:0',
        ]);

        $order = Order::with('phases.items')->findOrFail($validated['order_id']);

        DB::beginTransaction();
        try {
            // Handle non-phase damage items (return claims)
            if (!empty($validated['damages'])) {
                foreach ($validated['damages'] as $damage) {
                    ReturnItem::create([
                        'return_number'   => ReturnItem::generateReturnId(),
                        'product_id'      => $damage['item_id'],
                        'quantity'        => $damage['quantity'],
                        'reason'          => $damage['reason'],
                        'status'          => 'Pending',
                        'customer_name'   => $order->customer_name,
                        'order_reference' => $order->order_number,
                        'created_by'      => auth()->id(),
                    ]);

                    $item = Product::find($damage['item_id']);
                    if ($item) {
                        $prev = $item->stock;
                        $new  = max(0, $prev - $damage['quantity']);
                        $item->update(['stock' => $new]);
                        StockOut::create([
                            'product_id'       => $item->Product_Id,
                            'quantity'         => $damage['quantity'],
                            'previous_stock'   => $prev,
                            'new_stock'        => $new,
                            'reference_number' => 'SO-' . now()->format('YmdHis') . rand(10, 99),
                            'reason'           => 'Delivery Damage',
                            'notes'            => $damage['reason'],
                            'created_by'       => auth()->id(),
                            'created_at'       => now(),
                        ]);
                    }
                }
            }

            if (empty($validated['phase_id'])) {
                return response()->json(['success' => false, 'message' => 'Phase ID is required'], 422);
            }

            $phase = OrderPhase::with('items')->findOrFail($validated['phase_id']);
                $totalDamage = !empty($validated['phase_damages'])
                    ? array_sum(array_column($validated['phase_damages'], 'damage_qty'))
                    : 0;
                $phase->update(['damage_qty' => $totalDamage, 'status' => 'Delivered']);

                // Close out all assignments for the delivered phase so the order
                // is no longer counted as "assigned" and Phase 2 appears in Available Orders
                Assignment::where('order_number', $order->order_number)
                    ->where('phase_id', $phase->Phase_Id)
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->update(['status' => 'completed']);

                if (!empty($validated['phase_damages'])) {
                    $nextPhase = OrderPhase::where('order_id', $order->Order_Id)
                        ->where('phase_number', $phase->phase_number + 1)
                        ->with('items')
                        ->first();

                    if ($nextPhase) {
                        foreach ($validated['phase_damages'] as $dmg) {
                            if ($dmg['damage_qty'] <= 0) continue;
                            $nextItem = $nextPhase->items->firstWhere('name', $dmg['name']);
                            if ($nextItem) {
                                $carry   = $nextItem->damage_carry + $dmg['damage_qty'];
                                $nextItem->update([
                                    'damage_carry' => $carry,
                                    'required_qty' => $nextItem->base_qty + $carry,
                                ]);
                            } else {
                                OrderPhaseItem::create([
                                    'phase_id'      => $nextPhase->Phase_Id,
                                    'name'          => $dmg['name'],
                                    'base_qty'      => 0,
                                    'damage_carry'  => $dmg['damage_qty'],
                                    'required_qty'  => $dmg['damage_qty'],
                                    'completed_qty' => 0,
                                ]);
                            }
                        }
                    }
                }

                // If more phases are still pending, revert order to In-Progress for Phase 2 assignment
                $pendingPhases = OrderPhase::where('order_id', $order->Order_Id)
                    ->whereNotIn('status', ['Delivered'])
                    ->count();

                if ($pendingPhases > 0) {
                    $order->update(['status' => 'In-Progress']);
                } else {
                    $order->update(['status' => 'Delivered']);
                }

            DB::commit();

            $assignment = $order->assignments()->first();
            if ($assignment) {
                Notification::send(
                    $assignment->employee_id,
                    'order_delivered',
                    'Order Delivered',
                    "Order {$order->order_number} for {$order->customer_name} has been marked as delivered.",
                    ['order_id' => $order->order_number]
                );
            }

            $damageCount = count($validated['damages'] ?? []);
            $message = $damageCount > 0
                ? "Order delivered. {$damageCount} damage claim(s) recorded."
                : 'Order marked as delivered.';

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delivery failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
}