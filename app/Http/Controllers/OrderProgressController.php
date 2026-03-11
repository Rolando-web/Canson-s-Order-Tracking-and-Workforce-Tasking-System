<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhase;
use App\Models\OrderPhaseItem;
use App\Models\User;
use App\Models\Notification;

class OrderProgressController extends Controller
{
    /**
     * Show all orders with their phase progress.
     * Accessible by ALL authenticated users.
     */
    public function index()
    {
        $orders = Order::with(['phases.items'])
            ->whereNotIn('status', ['Ready for Delivery', 'Delivered', 'Cancelled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return $this->formatOrder($order);
            });

        return view('pages.order-progress', compact('orders'));
    }

    /**
     * Show a single order's progress.
     */
    public function show(Order $order)
    {
        $order->load(['phases.items']);
        $formatted = $this->formatOrder($order);
        return response()->json(['order' => $formatted]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'           => $order->order_number,
            'db_id'        => $order->Order_Id,
            'customer'     => $order->customer_name,
            'contact'      => $order->contact_number,
            'address'      => $order->delivery_address,
            'status'       => $order->status,
            'priority'     => $order->priority,
            'overall_pct'  => $order->overall_progress,
            'phases'       => $order->phases->map(fn($phase) => $this->formatPhase($phase))->toArray(),
        ];
    }

    private function formatPhase(OrderPhase $phase): array
    {
        $total    = $phase->items->sum('required_qty');
        $done     = $phase->items->sum('completed_qty');
        $pct      = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return [
            'id'            => $phase->Phase_Id,
            'phase_number'  => $phase->phase_number,
            'delivery_date' => $phase->delivery_date->format('M d, Y'),
            'delivery_raw'  => $phase->delivery_date->format('Y-m-d'),
            'status'        => $phase->status,
            'damage_qty'    => $phase->damage_qty,
            'total_qty'     => $total,
            'done_qty'      => $done,
            'pct'           => $pct,
            'items'         => $phase->items->map(fn($i) => [
                'id'            => $i->Phase_Item_Id,
                'name'          => $i->name,
                'required_qty'  => $i->required_qty,
                'base_qty'      => $i->base_qty,
                'damage_carry'  => $i->damage_carry,
                'completed_qty' => $i->completed_qty,
                'pct'           => $i->progress_percent,
                'remaining'     => $i->remaining,
            ])->toArray(),
        ];
    }

    /**
     * Update a phase item's completed_qty.
     * Any authenticated user (employee updating their own work).
     */
    public function updateItemProgress(Request $request, OrderPhaseItem $phaseItem)
    {
        $validated = $request->validate([
            'add_qty' => 'required|integer|min:1',
        ]);

        $maxAdd    = $phaseItem->required_qty - $phaseItem->completed_qty;
        $addQty    = min($validated['add_qty'], $maxAdd);
        $newDone   = $phaseItem->completed_qty + $addQty;

        $phaseItem->update(['completed_qty' => $newDone]);

        $phase = $phaseItem->phase()->with('items')->first();
        $allDone = $phase->items->every(fn($i) => $i->completed_qty >= $i->required_qty);

        if ($allDone && $phase->status !== 'Completed') {
            $phase->update(['status' => 'Completed']);

            $managerIds = User::where('role', 'admin')->orWhere('role', 'super_admin')->pluck('User_Id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'phase_completed',
                'Phase Completed',
                "Phase {$phase->phase_number} of order {$phase->order->order_number} has been completed.",
                ['order_id' => $phase->order->order_number]
            );
        }

        return response()->json([
            'success'       => true,
            'completed_qty' => $phaseItem->fresh()->completed_qty,
            'pct'           => $phaseItem->fresh()->progress_percent,
            'phase_done'    => $allDone,
            'phase_pct'     => $phase->progress_percent,
        ]);
    }

    /**
     * Record damage for a phase and auto-carry into the next phase.
     * Admin/super_admin only.
     */
    public function recordDamage(Request $request, OrderPhase $phase)
    {
        $validated = $request->validate([
            'damages'               => 'required|array|min:1',
            'damages.*.name'        => 'required|string',
            'damages.*.damage_qty'  => 'required|integer|min:0',
        ]);

        $totalDamage = array_sum(array_column($validated['damages'], 'damage_qty'));
        $phase->update(['damage_qty' => $totalDamage, 'status' => 'Delivered']);

        $nextPhase = OrderPhase::where('order_id', $phase->order_id)
            ->where('phase_number', $phase->phase_number + 1)
            ->with('items')
            ->first();

        if ($nextPhase) {
            foreach ($validated['damages'] as $dmg) {
                if ($dmg['damage_qty'] <= 0) continue;

                $nextItem = $nextPhase->items->firstWhere('name', $dmg['name']);
                if ($nextItem) {
                    $newCarry   = $nextItem->damage_carry + $dmg['damage_qty'];
                    $newRequired = $nextItem->base_qty + $newCarry;
                    $nextItem->update([
                        'damage_carry' => $newCarry,
                        'required_qty' => $newRequired,
                    ]);
                } else {
                    OrderPhaseItem::create([
                        'phase_id'     => $nextPhase->Phase_Id,
                        'name'         => $dmg['name'],
                        'base_qty'     => 0,
                        'damage_carry' => $dmg['damage_qty'],
                        'required_qty' => $dmg['damage_qty'],
                        'completed_qty'=> 0,
                    ]);
                }
            }

            $managerIds = User::where('role', 'admin')->orWhere('role', 'super_admin')->pluck('User_Id')->toArray();
            Notification::sendToMany(
                $managerIds,
                'damage_carry',
                'Damage Carry-Forward',
                "Phase {$phase->phase_number} of order {$phase->order->order_number} has {$totalDamage} damaged item(s) carried to Phase " . ($phase->phase_number + 1) . ".",
                ['order_id' => $phase->order->order_number]
            );
        }

        return response()->json(['success' => true, 'total_damage' => $totalDamage]);
    }
}