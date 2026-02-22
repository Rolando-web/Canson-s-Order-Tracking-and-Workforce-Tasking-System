<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === Stat Cards ===
        $totalOrders  = Order::count();
        $totalSales   = Order::where('status', 'Completed')->sum('total_amount');
        $pendingCount = Order::where('status', 'Pending')->count();

        $todaySales = Order::where('status', 'Completed')
            ->whereDate('updated_at', today())
            ->sum('total_amount');

        // % change calculations (vs last month)
        $thisMonth     = Order::whereMonth('created_at', now()->month)->count();
        $lastMonth     = Order::whereMonth('created_at', now()->subMonth()->month)->count();
        $ordersPctChange = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        $thisMonthSales = Order::where('status', 'Completed')->whereMonth('updated_at', now()->month)->sum('total_amount');
        $lastMonthSales = Order::where('status', 'Completed')->whereMonth('updated_at', now()->subMonth()->month)->sum('total_amount');
        $salesPctChange = $lastMonthSales > 0 ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) : 0;

        $yesterdaySales = Order::where('status', 'Completed')
            ->whereDate('updated_at', today()->subDay())
            ->sum('total_amount');
        $todayPctChange = $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : 0;

        // === Weekly Sales Chart ===
        $salesDays = [];
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();

        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $daySales = Order::where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->sum('total_amount');
            $dayOrders = Order::where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->count();

            $salesDays[$dayName] = [
                'amount' => (float)$daySales,
                'orders' => $dayOrders,
            ];
        }

        // === Weekly Production Chart (items produced / orders completed per day) ===
        $prodDays = [];
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = Order::where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->count() * 10; // approximate units per order
        }

        // === Recent Sales (completed orders) ===
        $recentSales = Order::where('status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id'          => $order->order_id,
                    'customer'    => $order->customer_name,
                    'date'        => $order->updated_at->diffForHumans(),
                    'amount'      => $order->total_amount,
                    'status'      => 'Completed',
                    'statusColor' => 'bg-green-50 text-green-600',
                ];
            })->toArray();

        // === Top Products ===
        $topProducts = OrderItem::select('name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', fn($q) => $q->where('status', 'Completed'))
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item, $index) {
                $maxSold = OrderItem::select(DB::raw('SUM(quantity) as total'))
                    ->whereHas('order', fn($q) => $q->where('status', 'Completed'))
                    ->groupBy('name')
                    ->orderByDesc('total')
                    ->limit(1)
                    ->value('total') ?? 1;

                return [
                    'name'    => $item->name,
                    'sold'    => (int)$item->total_sold,
                    'revenue' => (float)$item->total_revenue,
                    'pct'     => round(($item->total_sold / $maxSold) * 100),
                ];
            })->toArray();

        // === Order Status Distribution (for donut chart) ===
        $orderStatusCounts = [
            'Pending'     => Order::where('status', 'Pending')->count(),
            'In-Progress' => Order::where('status', 'In-Progress')->count(),
            'Completed'   => Order::where('status', 'Completed')->count(),
        ];

        return view('pages.dashboard', compact(
            'totalOrders', 'totalSales', 'pendingCount', 'todaySales',
            'ordersPctChange', 'salesPctChange', 'todayPctChange',
            'salesDays', 'prodDays', 'recentSales', 'topProducts', 'orderStatusCounts'
        ));
    }
}
