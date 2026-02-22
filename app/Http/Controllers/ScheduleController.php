<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleNote;
use App\Models\ActivityLog;

class ScheduleController extends Controller
{
    public function index()
    {
        $notes = ScheduleNote::with('creator')
            ->orderBy('schedule_date')
            ->get()
            ->map(function ($note) {
                return [
                    'id'            => $note->id,
                    'title'         => $note->title,
                    'description'   => $note->description,
                    'schedule_date' => $note->schedule_date->format('Y-m-d'),
                    'start_time'    => $note->start_time,
                    'end_time'      => $note->end_time,
                    'is_all_day'    => $note->is_all_day,
                    'priority'      => $note->priority,
                    'created_by'    => $note->creator ? $note->creator->name : 'System',
                ];
            });

        return view('pages.schedule', compact('notes'));
    }

    public function storeNote(Request $request)
    {
        $validated = $request->validate([
            'noteTitle'       => 'required|string|max:100',
            'noteDescription' => 'nullable|string',
            'noteDate'        => 'required|date',
            'notePriority'    => 'required|in:low,medium,high',
            'noteAllDay'      => 'sometimes|boolean',
            'noteStartTime'   => 'nullable|date_format:H:i',
            'noteEndTime'     => 'nullable|date_format:H:i',
        ]);

        $note = ScheduleNote::create([
            'title'         => $validated['noteTitle'],
            'description'   => $validated['noteDescription'] ?? null,
            'schedule_date' => $validated['noteDate'],
            'start_time'    => $validated['noteStartTime'] ?? null,
            'end_time'      => $validated['noteEndTime'] ?? null,
            'is_all_day'    => $request->boolean('noteAllDay'),
            'priority'      => $validated['notePriority'],
            'created_by'    => auth()->id(),
            'created_at'    => now(),
        ]);

        ActivityLog::log('Create Schedule', "Created schedule note: {$note->title} on {$validated['noteDate']}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }

        return redirect()->back()->with('success', 'Schedule note added.');
    }

    public function updateNote(Request $request, ScheduleNote $note)
    {
        $validated = $request->validate([
            'noteTitle'       => 'required|string|max:100',
            'noteDescription' => 'nullable|string',
            'noteDate'        => 'required|date',
            'notePriority'    => 'required|in:low,medium,high',
            'noteAllDay'      => 'sometimes|boolean',
            'noteStartTime'   => 'nullable|date_format:H:i',
            'noteEndTime'     => 'nullable|date_format:H:i',
        ]);

        $note->update([
            'title'         => $validated['noteTitle'],
            'description'   => $validated['noteDescription'] ?? null,
            'schedule_date' => $validated['noteDate'],
            'start_time'    => $validated['noteStartTime'] ?? null,
            'end_time'      => $validated['noteEndTime'] ?? null,
            'is_all_day'    => $request->boolean('noteAllDay'),
            'priority'      => $validated['notePriority'],
        ]);

        ActivityLog::log('Update Schedule', "Updated schedule note: {$note->title}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }

        return redirect()->back()->with('success', 'Schedule note updated.');
    }

    public function destroyNote(Request $request, ScheduleNote $note)
    {
        $title = $note->title;
        $note->delete();

        ActivityLog::log('Delete Schedule', "Deleted schedule note: {$title}");

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Schedule note deleted.');
    }
}
