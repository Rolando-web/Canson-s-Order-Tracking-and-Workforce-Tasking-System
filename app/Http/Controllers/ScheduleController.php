<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleNote;
use App\Models\Order;

class ScheduleController extends Controller
{
    public function index()
    {
        $notes = ScheduleNote::with('creator')
            ->orderBy('schedule_date')
            ->get()
            ->map(function ($note) {
                return [
                    'id'            => $note->Schedule_Note_Id,
                    'title'         => $note->title,
                    'description'   => $note->description,
                    'schedule_date' => $note->schedule_date->format('Y-m-d'),
                    'start_time'    => $note->start_time,
                    'end_time'      => $note->end_time,
                    'priority'      => $note->priority,
                    'created_by'    => $note->creator ? $note->creator->name : 'System',
                ];
            });

        $orders = Order::orderBy('delivery_date')
            ->whereNotIn('status', ['Completed', 'Delivered'])
            ->get()
            ->map(function ($order) {
                return [
                    'id'            => $order->Order_Id,
                    'order_id'      => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'start_date'    => $order->created_at->format('Y-m-d'),
                    'end_date'      => $order->delivery_date->format('Y-m-d'),
                    'priority'      => $order->priority,
                    'status'        => $order->status,
                ];
            });

        return view('pages.schedule', compact('notes', 'orders'));
    }

    public function storeNote(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $note = ScheduleNote::create([
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'schedule_date' => now()->toDateString(),
            'priority'      => 'medium',
            'created_by'    => auth()->id(),
            'created_at'    => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }

        return redirect()->back()->with('success', 'Schedule note added.');
    }

    public function updateNote(Request $request, ScheduleNote $note)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $note->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }

        return redirect()->back()->with('success', 'Schedule note updated.');
    }

    public function destroyNote(Request $request, ScheduleNote $note)
    {
        $note->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Schedule note deleted.');
    }
}