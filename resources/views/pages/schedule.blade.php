@extends('partials.app', ['title' => 'Schedule - Canson', 'activePage' => 'schedule'])

@push('styles')
    @vite('resources/css/pages/schedule.css')
@endpush

@php
    $scheduleData = ($notes ?? collect())->map(function ($n) {
        return [
            'id' => $n['id'] ?? $n->id ?? null,
            'title' => $n['title'] ?? $n->title ?? '',
            'description' => $n['description'] ?? $n->description ?? '',
            'schedule_date' => is_string($n['schedule_date'] ?? null) ? $n['schedule_date'] : (isset($n->schedule_date) ? $n->schedule_date->format('Y-m-d') : ''),
            'priority' => $n['priority'] ?? $n->priority ?? 'low',
        ];
    })->values();

    $orderData = ($orders ?? collect())->values();
@endphp

@push('scripts')
    <script>
        window.scheduleNotes = @json($scheduleData);
        window.scheduleOrders = @json($orderData);
    </script>
    @vite('resources/js/pages/schedule.js')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">AD</div>
        </div>
    </div>
@endsection

@section('content')
<div class="schedule-page">
    {{-- Header --}}
    <div class="schedule-header">
        <div class="schedule-header-left">
            <svg class="w-7 h-7 text-gray-700 mobile-hide" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Production Schedule</h2>
                <p class="text-gray-500 mt-0.5 mobile-hide">Track deadlines, production runs, and facility notes</p>
            </div>
        </div>
        <div class="schedule-header-right">
            {{-- View Toggle --}}
            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-emerald-600 text-white" data-view="month">Month</button>
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50" data-view="week">Week</button>
            </div>
            {{-- Navigation --}}
            <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2">
                <button id="prevPeriod" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span id="currentPeriod" class="text-sm font-semibold text-gray-700 min-w-[120px] text-center">{{ now()->format('F Y') }}</span>
                <button id="nextPeriod" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <button id="todayBtn" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Today</button>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-gray-200" id="dayHeaders">
            @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="px-3 py-3 text-xs font-semibold text-gray-500 text-center tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Calendar Grid - Will be dynamically populated --}}
        <div class="grid grid-cols-7" id="calendarGrid">
            {{-- Dynamically populated by JavaScript --}}
        </div>
    </div>

    {{-- View Note Detail Modal --}}
    <div id="viewNoteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeViewNoteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900" id="viewNoteTitle">Note</h3>
                    <button onclick="closeViewNoteModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600" id="viewNoteDescription">No description provided.</p>
                </div>
                <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200">
                    <button onclick="closeViewNoteModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Schedule Note Modal --}}
    <div id="scheduleNoteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeScheduleModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative" onclick="event.stopPropagation()">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Add Schedule Note</h3>
                    <button onclick="closeScheduleModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                        <input id="noteTitle" type="text" placeholder="Production deadline, Meeting, etc." 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea id="noteDescription" rows="3" placeholder="Add details about this note..."
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button onclick="closeScheduleModal()" 
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button onclick="saveScheduleNote()" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Save Note
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openViewNoteModal(title, description) {
    document.getElementById('viewNoteTitle').textContent = title;
    document.getElementById('viewNoteDescription').textContent = description || 'No description provided.';
    document.getElementById('viewNoteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
window.openViewNoteModal = openViewNoteModal;

function closeViewNoteModal() {
    document.getElementById('viewNoteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
window.closeViewNoteModal = closeViewNoteModal;

function closeScheduleModal() {
    document.getElementById('scheduleNoteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openScheduleModal(date = null) {
    document.getElementById('scheduleNoteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function saveScheduleNote() {
    const data = {
        title: document.getElementById('noteTitle').value,
        description: document.getElementById('noteDescription').value,
        _token: document.querySelector('meta[name="csrf-token"]')?.content
    };

    fetch('{{ route("schedule.notes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': data._token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(() => { closeScheduleModal(); location.reload(); })
    .catch(err => { console.error(err); alert('Failed to save note'); });
}

// Add button to open modal when clicking on calendar dates
document.addEventListener('DOMContentLoaded', () => {
    // You can add click handlers to calendar dates here
    const addNoteBtn = document.createElement('button');
    addNoteBtn.className = 'fixed bottom-8 right-8 w-14 h-14 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg flex items-center justify-center transition-colors';
    addNoteBtn.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>';
    addNoteBtn.onclick = () => openScheduleModal();
    document.querySelector('.schedule-page').appendChild(addNoteBtn);
});
</script>
@endsection
