<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders  = Order::count();
        $totalSales   = Order::sum('total_amount');
        $pendingCount = Order::where('status', 'Pending')->count();

        $todaySales = Order::whereDate('created_at', today())->sum('total_amount');

        $thisMonth     = Order::whereMonth('created_at', now()->month)->count();
        $lastMonth     = Order::whereMonth('created_at', now()->subMonth()->month)->count();
        $ordersPctChange = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        $thisMonthSales = Order::whereMonth('created_at', now()->month)->sum('total_amount');
        $lastMonthSales = Order::whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');
        $salesPctChange = $lastMonthSales > 0 ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) : 0;

        $yesterdaySales = Order::whereDate('created_at', today()->subDay())->sum('total_amount');
        $todayPctChange = $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : 0;

        $salesDays = [];
        $dayNames  = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();

        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $salesDays[$dayName] = [
                'amount' => (float) Order::whereDate('created_at', $date)->sum('total_amount'),
                'orders' => (int)   Order::whereDate('created_at', $date)->count(),
            ];
        }

        $prodDays = [];
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = (int) OrderPhaseItem::whereHas('phase.order', function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            })->sum('base_qty');
        }

        $recentSales = Order::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id'          => $order->order_number,
                    'customer'    => $order->customer_name,
                    'date'        => $order->created_at->diffForHumans(),
                    'amount'      => $order->total_amount,
                    'status'      => $order->status,
                    'statusColor' => match($order->status) {
                        'Completed'   => 'bg-green-50 text-green-600',
                        'In-Progress' => 'bg-emerald-50 text-emerald-600',
                        default       => 'bg-gray-50 text-gray-500',
                    },
                ];
            })->toArray();

        $maxSold = (int) (OrderPhaseItem::select(DB::raw('SUM(base_qty) as total'))
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(1)
            ->value('total') ?? 1);

        $topProducts = OrderPhaseItem::select('name', DB::raw('SUM(base_qty) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($maxSold) {
                return [
                    'name'    => $item->name,
                    'sold'    => (int)$item->total_sold,
                    'revenue' => (float)$item->total_revenue,
                    'pct'     => round(($item->total_sold / $maxSold) * 100),
                ];
            })->toArray();

        $orderStatusCounts = [
            'Pending'     => Order::where('status', 'Pending')->count(),
            'In-Progress' => Order::where('status', 'In-Progress')->count(),
            'Completed'   => Order::where('status', 'Completed')->count(),
        ];

        // Inventory widgets
        $inventoryItemCount  = Product::count();
        $lowStockCount       = Product::where('status', 'Low Stock')->orWhere('status', 'Out of Stock')->count();

        return view('pages.dashboard', compact(
            'totalOrders', 'totalSales', 'pendingCount', 'todaySales',
            'ordersPctChange', 'salesPctChange', 'todayPctChange',
            'salesDays', 'prodDays', 'recentSales', 'topProducts', 'orderStatusCounts',
            'inventoryItemCount', 'lowStockCount'
        ));
    }

    public function salesData(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $data   = [];

        if ($period === 'weekly') {
            $days  = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $start = now()->startOfWeek(Carbon::MONDAY);
            foreach ($days as $i => $label) {
                $date = $start->copy()->addDays($i);
                $data[$label] = [
                    'amount' => (float) Order::whereDate('created_at', $date)->sum('total_amount'),
                    'orders' => (int)   Order::whereDate('created_at', $date)->count(),
                ];
            }
        } elseif ($period === 'monthly') {
            $start = now()->startOfMonth();
            $end   = now()->endOfMonth();
            for ($w = 0; $w < 5; $w++) {
                $wStart = $start->copy()->addWeeks($w);
                if ($wStart->gt($end)) break;
                $wEnd = $wStart->copy()->addDays(6)->endOfDay();
                if ($wEnd->gt($end)) $wEnd = $end->copy()->endOfDay();
                $label = 'Wk ' . ($w + 1);
                $data[$label] = [
                    'amount' => (float) Order::whereBetween('created_at', [$wStart->startOfDay(), $wEnd])->sum('total_amount'),
                    'orders' => (int)   Order::whereBetween('created_at', [$wStart->startOfDay(), $wEnd])->count(),
                ];
            }
        } elseif ($period === 'yearly') {
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            foreach ($months as $i => $label) {
                $data[$label] = [
                    'amount' => (float) Order::whereYear('created_at', now()->year)->whereMonth('created_at', $i + 1)->sum('total_amount'),
                    'orders' => (int)   Order::whereYear('created_at', now()->year)->whereMonth('created_at', $i + 1)->count(),
                ];
            }
        }

        return response()->json($data);
    }
}