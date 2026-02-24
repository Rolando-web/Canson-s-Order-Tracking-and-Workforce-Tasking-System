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
        $totalRevenue    = Order::sum('total_amount');
        $totalTransactions = Order::count();
        $avgOrderValue   = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // This month's revenue (filter by year AND month using created_at)
        $thisMonthRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        $thisMonthTransactions = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Last month's revenue for trend comparison
        $lastMonth = now()->subMonth();
        $lastMonthRevenue = Order::whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('total_amount');
        $revenuePctChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // === Sales Trend Chart (last 7 days) ===
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayLabel = $date->format('M d');
            $daySales = Order::whereDate('created_at', $date)
                ->sum('total_amount');
            $dayCount = Order::whereDate('created_at', $date)
                ->count();

            $salesTrend[$dayLabel] = [
                'amount' => (float)$daySales,
                'orders' => $dayCount,
            ];
        }

        // === Sales Transactions (all orders - already paid) ===
        $query = Order::with('items')
            ->orderBy('created_at', 'desc');

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
                'status'      => $order->status,
                'statusColor' => match($order->status) {
                    'Completed' => 'bg-green-500',
                    'In-Progress' => 'bg-emerald-500',
                    default => 'bg-gray-400',
                },
                'date'        => $order->created_at->format('M d, Y'),
            ];
        });

        $salesPaginated->setCollection($sales);

        // === Quick Insights ===
        $topCategory = OrderItem::select('inventory_item_id', DB::raw('SUM(subtotal) as total'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('total')
            ->first();

        $topCategoryName = $topCategory && $topCategory->inventoryItem
            ? $topCategory->inventoryItem->category : 'N/A';

        $todaySalesAmount = Order::whereDate('created_at', today())
            ->sum('total_amount');

        return view('pages.sales', compact(
            'totalRevenue', 'totalTransactions', 'avgOrderValue',
            'thisMonthRevenue', 'thisMonthTransactions',
            'revenuePctChange', 'salesTrend', 'salesPaginated',
            'topCategoryName', 'todaySalesAmount'
        ));
    }
}
