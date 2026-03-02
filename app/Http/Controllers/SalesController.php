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
        $totalRevenue      = Order::sum('total_amount');
        $totalTransactions = Order::count();
        $avgOrderValue     = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $thisMonthRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        $thisMonthTransactions = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonth        = now()->subMonth();
        $lastMonthRevenue = Order::whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('total_amount');
        $revenuePctChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // === Sales Trend Chart – respects ?period=daily|weekly|monthly ===
        $period     = $request->input('period', 'daily');
        $salesTrend = [];

        if ($period === 'weekly') {
            // Last 8 weeks  (Mon–Sun buckets)
            for ($i = 7; $i >= 0; $i--) {
                $weekStart = now()->startOfWeek()->subWeeks($i);
                $weekEnd   = $weekStart->copy()->endOfWeek();
                $label     = $weekStart->format('M d');
                $salesTrend[$label] = [
                    'amount' => (float) Order::whereBetween('created_at', [$weekStart, $weekEnd])->sum('total_amount'),
                    'orders' => (int)   Order::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                ];
            }
        } elseif ($period === 'monthly') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date  = now()->subMonths($i);
                $label = $date->format('M Y');
                $salesTrend[$label] = [
                    'amount' => (float) Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum('total_amount'),
                    'orders' => (int)   Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                ];
            }
        } else {
            // Daily – last 7 days (default)
            for ($i = 6; $i >= 0; $i--) {
                $date  = now()->subDays($i);
                $label = $date->format('M d');
                $salesTrend[$label] = [
                    'amount' => (float) Order::whereDate('created_at', $date)->sum('total_amount'),
                    'orders' => (int)   Order::whereDate('created_at', $date)->count(),
                ];
            }
        }

        // === Sales Transactions Table ===
        $query = Order::with('items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_id', 'like', "%{$s}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $salesPaginated = $query->paginate(10)->withQueryString();

        $sales = $salesPaginated->getCollection()->map(function ($order) {
            return [
                'id'          => $order->order_id,
                'db_id'       => $order->id,
                'customer'    => $order->customer_name,
                'contact'     => $order->contact_number,
                'address'     => $order->delivery_address,
                'items'       => $order->items->map(fn($i) => $i->name)->implode(', ') ?: 'N/A',
                'qty'         => $order->items->sum('quantity'),
                'amount'      => $order->total_amount,
                'status'      => $order->status,
                'statusColor' => match($order->status) {
                    'Completed'        => 'bg-green-500',
                    'In-Progress'      => 'bg-emerald-500',
                    'Ready for Delivery' => 'bg-blue-500',
                    'Delivered'        => 'bg-teal-500',
                    default            => 'bg-gray-400',
                },
                'statusBadge' => match($order->status) {
                    'Completed'        => 'bg-green-50 text-green-700',
                    'In-Progress'      => 'bg-emerald-50 text-emerald-700',
                    'Ready for Delivery' => 'bg-blue-50 text-blue-700',
                    'Delivered'        => 'bg-teal-50 text-teal-700',
                    default            => 'bg-gray-100 text-gray-600',
                },
                'date'     => $order->created_at->format('M d, Y'),
                'notes'    => $order->notes,
                'priority' => $order->priority,
                'order_items' => $order->items->map(fn($i) => [
                    'name'     => $i->name,
                    'qty'      => $i->quantity,
                    'price'    => $i->unit_price,
                    'subtotal' => $i->subtotal,
                ])->toArray(),
            ];
        });

        $salesPaginated->setCollection($sales);

        // === Quick Insights ===
        $topCategory = OrderItem::select('inventory_item_id', DB::raw('SUM(subtotal) as total'))
            ->groupBy('inventory_item_id')->orderByDesc('total')->first();
        $topCategoryName = $topCategory && $topCategory->inventoryItem
            ? $topCategory->inventoryItem->category : 'N/A';

        $todaySalesAmount = Order::whereDate('created_at', today())->sum('total_amount');

        return view('pages.sales', compact(
            'totalRevenue', 'totalTransactions', 'avgOrderValue',
            'thisMonthRevenue', 'thisMonthTransactions',
            'revenuePctChange', 'salesTrend', 'salesPaginated',
            'topCategoryName', 'todaySalesAmount', 'period'
        ));
    }

    // ---------------------------------------------------------------
    // Export CSV
    // ---------------------------------------------------------------
    public function exportCsv(Request $request)
    {
        $query = Order::with('items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_id', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->get();

        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Transaction ID', 'Customer', 'Contact', 'Items', 'Qty', 'Amount (PHP)', 'Status', 'Date']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_id,
                    $order->customer_name,
                    $order->contact_number,
                    $order->items->map(fn($i) => $i->name)->implode('; '),
                    $order->items->sum('quantity'),
                    number_format($order->total_amount, 2),
                    $order->status,
                    $order->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ---------------------------------------------------------------
    // Reports Index Page
    // ---------------------------------------------------------------
    public function reportsIndex()
    {
        $totalRevenue      = Order::sum('total_amount');
        $totalOrders       = Order::count();
        $thisMonthRevenue  = Order::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total_amount');
        $todaySales        = Order::whereDate('created_at', today())->sum('total_amount');

        // Monthly revenue last 12 months
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue[$date->format('M Y')] = (float) Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum('total_amount');
        }

        $statusCounts = [
            'Pending'           => Order::where('status', 'Pending')->count(),
            'In-Progress'       => Order::where('status', 'In-Progress')->count(),
            'Ready for Delivery'=> Order::where('status', 'Ready for Delivery')->count(),
            'Delivered'         => Order::where('status', 'Delivered')->count(),
            'Completed'         => Order::where('status', 'Completed')->count(),
        ];

        $topProducts = OrderItem::select('name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('name')->orderByDesc('total_revenue')->limit(5)->get();

        $recentOrders = Order::with('items')->orderBy('created_at', 'desc')->limit(10)->get()->map(fn($o) => [
            'id'       => $o->order_id,
            'customer' => $o->customer_name,
            'items'    => $o->items->map(fn($i) => $i->name)->implode(', ') ?: 'N/A',
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        return view('pages.reports.index', compact(
            'totalRevenue', 'totalOrders', 'thisMonthRevenue', 'todaySales',
            'monthlyRevenue', 'statusCounts', 'topProducts', 'recentOrders'
        ));
    }

    // ---------------------------------------------------------------
    // Print Report (printable HTML view)
    // ---------------------------------------------------------------
    public function printReport(Request $request)
    {
        $query = Order::with('items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_id', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->get()->map(fn($o) => [
            'id'       => $o->order_id,
            'customer' => $o->customer_name,
            'contact'  => $o->contact_number,
            'items'    => $o->items->map(fn($i) => $i->name)->implode(', ') ?: 'N/A',
            'qty'      => $o->items->sum('quantity'),
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        $totalRevenue  = $orders->sum('amount');
        $totalOrders   = $orders->count();
        $generatedAt   = now()->format('F d, Y h:i A');

        return view('pages.reports.sales-print', compact('orders', 'totalRevenue', 'totalOrders', 'generatedAt'));
    }
}
