<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Product::orderBy('item_code')->get();
        $totalItems = $items->count();
        $lowStockAlert = $items->where('status', 'Low Stock')->count();

        // Reports data
        $lowStockItems = $items->whereIn('status', ['Low Stock', 'Out of Stock'])->values();

        $valuationByCategory = $items->groupBy('category')->map(function ($group, $category) {
            return (object) [
                'category'    => $category,
                'item_count'  => $group->count(),
                'total_stock' => $group->sum('stock'),
                'total_value' => $group->sum(fn($p) => $p->stock * $p->unit_price),
            ];
        })->values();

        $recentStockIn  = StockIn::with('product')->orderBy('created_at', 'desc')->limit(10)->get();
        $recentStockOut = StockOut::with('product')->orderBy('created_at', 'desc')->limit(10)->get();

        return view('pages.inventory', compact(
            'items', 'totalItems', 'lowStockAlert',
            'lowStockItems', 'valuationByCategory', 'recentStockIn', 'recentStockOut'
        ));
    }

    public function stockInPage()
    {
        $items = Product::orderBy('name')->get();

        $allTransactions = StockIn::with(['product', 'creator', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group into batches by reference number (1 row per receipt/batch)
        $batches = $allTransactions
            ->groupBy('reference_number')
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'reference_number' => $first->reference_number,
                    'created_at'       => $first->created_at,
                    'supplier'         => $first->supplier,
                    'notes'            => $first->notes,
                    'creator'          => $first->creator,
                    'items'            => $group,
                    'total_qty'        => $group->sum('quantity'),
                    'total_cost'       => $group->sum(fn($i) => $i->quantity * $i->unit_cost),
                    'item_count'       => $group->count(),
                ];
            })
            ->values()
            ->take(50);

        $todayCount = StockIn::whereDate('created_at', today())->count();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $archivedSuppliers = Supplier::where('archived', true)->orderBy('name')->get();

        return view('pages.stock-in', compact('items', 'batches', 'todayCount', 'suppliers', 'archivedSuppliers'));
    }

    public function stockOutPage()
    {
        $allTransactions = StockOut::with(['product', 'creator', 'order.phases'])
            ->orderBy('created_at', 'desc')
            ->get();

        $batches = $allTransactions
            ->groupBy('reference_number')
            ->map(function ($group) {
                $first = $group->first();
                $order = $first->order;

                return (object) [
                    'reference_number' => $first->reference_number,
                    'created_at'       => $first->created_at,
                    'reason'           => $first->reason,
                    'notes'            => $first->notes,
                    'creator'          => $first->creator,
                    'items'            => $group,
                    'total_qty'        => $group->sum('quantity'),
                    'item_count'       => $group->count(),
                    'order'            => $order,
                    'order_number'     => $order ? $order->order_number : null,
                    'customer_name'    => $order ? $order->customer_name : null,
                    'order_status'     => $order ? $order->status : null,
                    'phases'           => $order && $order->phases->isNotEmpty()
                        ? $order->phases->sortBy('phase_number')->map(fn($p) => (object) [
                            'number'   => $p->phase_number,
                            'status'   => $p->status,
                            'delivery' => $p->delivery_date->format('M d, Y'),
                        ])->values()->toArray()
                        : [],
                ];
            })
            ->values()
            ->take(50);

        $todayCount = StockOut::whereDate('created_at', today())->count();

        return view('pages.stock-out', compact('batches', 'todayCount'));
    }

    public function products()
    {
        $items = Product::orderBy('item_code')->get();
        $categories = $items->pluck('category')->unique()->filter()->values();

        return view('pages.products', compact('items', 'categories'));
    }

    public function updateProduct(Request $request, Product $item)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'unit_price'    => 'required|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:1',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
              Storage::disk('public')->makeDirectory('Product');
              $validated['image_path'] = $request->file('image')->store('Product', 'public');
        }

        unset($validated['image']);

        $item->update($validated);
        $item->updateStockStatus();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string|max:50',
            'unit_price'    => 'nullable|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'reorder_point' => 'nullable|integer|min:1',
            'status'        => 'nullable|string|max:50',
            'image'         => 'nullable|image|max:2048',
        ]);

        $lastItem = Product::orderBy('Product_Id', 'desc')->first();
        $nextId = $lastItem ? intval(str_replace('INV-', '', $lastItem->item_code)) + 1 : 1;
        $validated['item_code'] = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        if (empty($validated['status'])) {
            $validated['status'] = 'In Stock'; // will be corrected by updateStockStatus()
        }

        if ($request->hasFile('image')) {
                Storage::disk('public')->makeDirectory('Product');
                $validated['image_path'] = $request->file('image')->store('Product', 'public');
        }

        unset($validated['image']);

        $item = Product::create($validated);
        $item->updateStockStatus();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $item)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'category'   => 'sometimes|string|max:255',
            'unit'       => 'sometimes|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'status'     => 'sometimes|string|max:50',
        ]);

        $item->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Item updated successfully.');
    }

    public function destroy(Request $request, Product $item)
    {
        $item->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Item deleted successfully.');
    }

    public function bulkStockIn(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.item_id'    => 'required|exists:products,Product_Id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_cost'  => 'nullable|numeric|min:0',
            'supplier_id'        => 'nullable|exists:suppliers,Supplier_Id',
            'notes'              => 'nullable|string',
        ]);

        $reference = 'SI-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $now = now();

        foreach ($validated['items'] as $entry) {
            $item = Product::findOrFail($entry['item_id']);
            $previousStock = $item->stock;
            $newStock = $previousStock + $entry['quantity'];

            $item->update(['stock' => $newStock]);
            $item->updateStockStatus();

            StockIn::create([
                'product_id'       => $item->Product_Id,
                'quantity'         => $entry['quantity'],
                'previous_stock'   => $previousStock,
                'new_stock'        => $newStock,
                'unit_cost'        => $entry['unit_cost'] ?? 0,
                'reference_number' => $reference,
                'supplier_id'      => $validated['supplier_id'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'created_by'       => auth()->id(),
                'created_at'       => $now,
            ]);
        }

        return response()->json(['success' => true, 'count' => count($validated['items']), 'reference' => $reference]);
    }

    // ========== Supplier CRUD ==========

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:50',
            'address' => 'required|string',
            'email'   => 'required|email|max:50',
            'phone'   => 'required|string|max:11',
        ]);

        $supplier = Supplier::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->back()->with('success', 'Supplier added successfully.');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:50',
            'address' => 'required|string',
            'email'   => 'required|email|max:50',
            'phone'   => 'required|string|max:11',
        ]);

        $supplier->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->back()->with('success', 'Supplier updated successfully.');
    }

    public function destroySupplier(Request $request, Supplier $supplier)
    {
        $supplier->update(['archived' => true]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Supplier removed successfully.');
    }
}