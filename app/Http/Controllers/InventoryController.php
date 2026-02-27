<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\User;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('item_id')->get();
        $totalItems = $items->count();
        $lowStockAlert = $items->where('stock', '<', 50)->count();
        
        return view('pages.inventory', compact('items', 'totalItems', 'lowStockAlert'));
    }

    /**
     * Stock In page — list items for stock in + movement history
     */
    public function stockInPage()
    {
        $items = InventoryItem::orderBy('name')->get();
        $transactions = StockTransaction::with(['inventoryItem', 'creator'])
            ->where('transaction_type', 'stock_in')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        $todayCount = StockTransaction::where('transaction_type', 'stock_in')
            ->whereDate('transaction_date', today())
            ->count();

        return view('pages.stock-in', compact('items', 'transactions', 'todayCount'));
    }

    /**
     * Stock Out page — shows automatic stock outs from assignments + history
     */
    public function stockOutPage()
    {
        $items = InventoryItem::orderBy('name')->get();
        $transactions = StockTransaction::with(['inventoryItem', 'creator'])
            ->where('transaction_type', 'stock_out')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        $todayCount = StockTransaction::where('transaction_type', 'stock_out')
            ->whereDate('transaction_date', today())
            ->count();

        return view('pages.stock-out', compact('items', 'transactions', 'todayCount'));
    }

    public function products()
    {
        $items = InventoryItem::orderBy('item_id')->get();
        $categories = $items->pluck('category')->unique()->filter()->values();
        
        return view('pages.products', compact('items', 'categories'));
    }

    public function updateProduct(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'category'   => 'required|string|max:255',
            'unit'       => 'required|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'stock'      => 'required|integer|min:0',
            'status'     => 'nullable|string|max:50',
            'image'      => 'nullable|image|max:2048',
        ]);

        // Generate next item_id
        $lastItem = InventoryItem::orderBy('id', 'desc')->first();
        $nextId = $lastItem ? intval(str_replace('INV-', '', $lastItem->item_id)) + 1 : 1;
        $validated['item_id'] = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Set default status
        if (empty($validated['status'])) {
            $validated['status'] = $validated['stock'] > 0 ? 'In Stock' : 'Out of Stock';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($validated['image']);

        $item = InventoryItem::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    public function update(Request $request, InventoryItem $item)
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

    public function destroy(Request $request, InventoryItem $item)
    {
        $name = $item->name;
        $item->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Item deleted successfully.');
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'item_id'   => 'required|exists:inventory_items,id',
            'quantity'  => 'required|integer|min:1',
            'supplier'  => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
        ]);

        $item = InventoryItem::findOrFail($validated['item_id']);
        $previousStock = $item->stock;
        $newStock = $previousStock + $validated['quantity'];

        $item->update([
            'stock'  => $newStock,
            'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
        ]);

        StockTransaction::create([
            'item_id'          => $item->id,
            'transaction_type' => 'stock_in',
            'quantity'         => $validated['quantity'],
            'previous_stock'   => $previousStock,
            'new_stock'        => $newStock,
            'reference_number' => StockTransaction::generateReference('stock_in'),
            'supplier'         => $validated['supplier'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'transaction_date' => now()->toDateString(),
            'created_by'       => auth()->id(),
            'created_at'       => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item->fresh()]);
        }

        return redirect()->back()->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'item_id'  => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string|max:100',
            'notes'    => 'nullable|string',
        ]);

        $item = InventoryItem::findOrFail($validated['item_id']);
        $previousStock = $item->stock;

        if ($validated['quantity'] > $previousStock) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Insufficient stock.'], 422);
            }
            return redirect()->back()->with('error', 'Insufficient stock.');
        }

        $newStock = $previousStock - $validated['quantity'];

        $item->update([
            'stock'  => $newStock,
            'status' => $newStock > 0 ? ($newStock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock',
        ]);

        StockTransaction::create([
            'item_id'          => $item->id,
            'transaction_type' => 'stock_out',
            'quantity'         => $validated['quantity'],
            'previous_stock'   => $previousStock,
            'new_stock'        => $newStock,
            'reference_number' => StockTransaction::generateReference('stock_out'),
            'reason'           => $validated['reason'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'transaction_date' => now()->toDateString(),
            'created_by'       => auth()->id(),
            'created_at'       => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item->fresh()]);
        }

        return redirect()->back()->with('success', 'Stock removed successfully.');
    }
}
