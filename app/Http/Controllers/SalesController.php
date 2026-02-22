<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        // === Summary Cards ===
        $totalRevenue    = Order::where('status', 'Completed')->sum('total_amount');
        $totalTransactions = Order::where('status', 'Completed')->count();
        $avgOrderValue   = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Monthly revenue for trend
        $thisMonthRevenue = Order::where('status', 'Completed')
            ->whereMonth('updated_at', now()->month)
            ->sum('total_amount');
        $lastMonthRevenue = Order::where('status', 'Completed')
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->sum('total_amount');
        $revenuePctChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // === Sales Trend Chart (last 7 days) ===
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayLabel = $date->format('M d');
            $daySales = Order::where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->sum('total_amount');
            $dayCount = Order::where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->count();

            $salesTrend[$dayLabel] = [
                'amount' => (float)$daySales,
                'orders' => $dayCount,
            ];
        }

        // === Sales Transactions (completed orders) ===
        $query = Order::where('status', 'Completed')
            ->with('items')
            ->orderBy('updated_at', 'desc');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        $salesPaginated = $query->paginate(10);

        $sales = $salesPaginated->getCollection()->map(function ($order) {
            $itemsSummary = $order->items->map(fn($i) => $i->name)->implode(', ');
            $totalQty     = $order->items->sum('quantity');

            return [
                'id'          => $order->order_id,
                'customer'    => $order->customer_name,
                'contact'     => $order->contact_number,
                'items'       => $itemsSummary ?: 'N/A',
                'qty'         => $totalQty,
                'amount'      => $order->total_amount,
                'status'      => 'Completed',
                'statusColor' => 'bg-green-500',
                'date'        => $order->updated_at->format('M d, Y'),
            ];
        });

        $salesPaginated->setCollection($sales);

        // === Quick Insights ===
        $topCategory = OrderItem::select('inventory_item_id', DB::raw('SUM(subtotal) as total'))
            ->whereHas('order', fn($q) => $q->where('status', 'Completed'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('total')
            ->first();

        $topCategoryName = $topCategory && $topCategory->inventoryItem
            ? $topCategory->inventoryItem->category : 'N/A';

        $todaySalesAmount = Order::where('status', 'Completed')
            ->whereDate('updated_at', today())
            ->sum('total_amount');

        return view('pages.sales', compact(
            'totalRevenue', 'totalTransactions', 'avgOrderValue',
            'revenuePctChange', 'salesTrend', 'salesPaginated',
            'topCategoryName', 'todaySalesAmount'
        ));
    }
}
