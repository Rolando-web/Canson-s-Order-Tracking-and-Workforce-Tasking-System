<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index(Request $request)
    {
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

        $period     = $request->input('period', 'daily');
        $salesTrend = [];

        if ($period === 'weekly') {
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
            for ($i = 11; $i >= 0; $i--) {
                $date  = now()->subMonths($i);
                $label = $date->format('M Y');
                $salesTrend[$label] = [
                    'amount' => (float) Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum('total_amount'),
                    'orders' => (int)   Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                ];
            }
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date  = now()->subDays($i);
                $label = $date->format('M d');
                $salesTrend[$label] = [
                    'amount' => (float) Order::whereDate('created_at', $date)->sum('total_amount'),
                    'orders' => (int)   Order::whereDate('created_at', $date)->count(),
                ];
            }
        }

        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $salesPaginated = $query->paginate(10)->withQueryString();

        $sales = $salesPaginated->getCollection()->map(function ($order) {
            $allItems = $order->phases->flatMap->items;
            $uniqueNames = $allItems->pluck('name')->unique()->implode(', ') ?: 'N/A';

            return [
                'id'          => $order->order_number,
                'db_id'       => $order->Order_Id,
                'customer'    => $order->customer_name,
                'contact'     => $order->contact_number,
                'address'     => $order->delivery_address,
                'items'       => $uniqueNames,
                'qty'         => $allItems->sum('base_qty'),
                'amount'      => $order->total_amount,
                'status'      => $order->status,
                'statusColor' => match($order->status) {
                    'Completed'          => 'bg-green-500',
                    'In-Progress'        => 'bg-emerald-500',
                    'Ready for Delivery' => 'bg-blue-500',
                    'Delivered'          => 'bg-teal-500',
                    default              => 'bg-gray-400',
                },
                'statusBadge' => match($order->status) {
                    'Completed'          => 'bg-green-50 text-green-700',
                    'In-Progress'        => 'bg-emerald-50 text-emerald-700',
                    'Ready for Delivery' => 'bg-blue-50 text-blue-700',
                    'Delivered'          => 'bg-teal-50 text-teal-700',
                    default              => 'bg-gray-100 text-gray-600',
                },
                'date'        => $order->created_at->format('M d, Y'),
                'notes'       => $order->notes,
                'priority'    => $order->priority,
                'order_items' => $order->phases->first()?->items->map(fn($i) => [
                    'name'     => $i->name,
                    'qty'      => $i->base_qty,
                    'price'    => $i->unit_price,
                    'subtotal' => $i->subtotal,
                ])->toArray() ?? [],
            ];
        });

        $salesPaginated->setCollection($sales);

        $topCategory = OrderPhaseItem::select('product_id', DB::raw('SUM(subtotal) as total'))
            ->groupBy('product_id')->orderByDesc('total')->first();
        $topCategoryName = $topCategory && $topCategory->product
            ? $topCategory->product->category : 'N/A';

        $todaySalesAmount = Order::whereDate('created_at', today())->sum('total_amount');

        return view('pages.sales', compact(
            'totalRevenue', 'totalTransactions', 'avgOrderValue',
            'thisMonthRevenue', 'thisMonthTransactions',
            'revenuePctChange', 'salesTrend', 'salesPaginated',
            'topCategoryName', 'todaySalesAmount', 'period'
        ));
    }

    public function exportCsv(Request $request)
    {
        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders   = $query->get();
        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Transaction ID', 'Customer', 'Contact', 'Items', 'Qty', 'Amount (PHP)', 'Status', 'Date']);

            foreach ($orders as $order) {
                $allItems = $order->phases->flatMap->items;
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->contact_number,
                    $allItems->pluck('name')->unique()->implode('; '),
                    $allItems->sum('base_qty'),
                    number_format($order->total_amount, 2),
                    $order->status,
                    $order->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reportsIndex()
    {
        $totalRevenue     = Order::sum('total_amount');
        $totalOrders      = Order::count();
        $thisMonthRevenue = Order::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total_amount');
        $todaySales       = Order::whereDate('created_at', today())->sum('total_amount');

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue[$date->format('M Y')] = (float) Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum('total_amount');
        }

        $statusCounts = [
            'Pending'            => Order::where('status', 'Pending')->count(),
            'In-Progress'        => Order::where('status', 'In-Progress')->count(),
            'Ready for Delivery' => Order::where('status', 'Ready for Delivery')->count(),
            'Delivered'          => Order::where('status', 'Delivered')->count(),
            'Completed'          => Order::where('status', 'Completed')->count(),
        ];

        $topProducts = OrderPhaseItem::select('name', DB::raw('SUM(base_qty) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('name')->orderByDesc('total_revenue')->limit(5)->get();

        $recentOrders = Order::with('phases.items')->orderBy('created_at', 'desc')->limit(10)->get()->map(fn($o) => [
            'id'       => $o->order_number,
            'customer' => $o->customer_name,
            'items'    => $o->phases->flatMap->items->pluck('name')->unique()->implode(', ') ?: 'N/A',
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        return view('pages.reports.index', compact(
            'totalRevenue', 'totalOrders', 'thisMonthRevenue', 'todaySales',
            'monthlyRevenue', 'statusCounts', 'topProducts', 'recentOrders'
        ));
    }

    public function printReport(Request $request)
    {
        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->get()->map(fn($o) => [
            'id'       => $o->order_number,
            'customer' => $o->customer_name,
            'contact'  => $o->contact_number,
            'items'    => $o->phases->flatMap->items->pluck('name')->unique()->implode(', ') ?: 'N/A',
            'qty'      => $o->phases->flatMap->items->sum('base_qty'),
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        $totalRevenue = $orders->sum('amount');
        $totalOrders  = $orders->count();
        $generatedAt  = now()->format('F d, Y h:i A');

        return view('pages.reports.sales-print', compact('orders', 'totalRevenue', 'totalOrders', 'generatedAt'));
    }
}