@extends('partials.app', ['title' => 'Activity Logs - Canson', 'activePage' => 'activity-logs'])

@push('styles')
    <style>
        .activity-logs-page {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }
    </style>
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
<div class="activity-logs-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Activity Logs</h2>
            <p class="text-gray-500 mt-1">Track all system activities and user actions</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export Logs
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" placeholder="Search activities..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <select class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option>All Users</option>
                <option>Admin User</option>
                <option>System Admin</option>
                <option>Juan Dela Cruz</option>
                <option>Maria Santos</option>
            </select>
            <select class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option>All Actions</option>
                <option>Login</option>
                <option>Logout</option>
                <option>Create</option>
                <option>Update</option>
                <option>Delete</option>
            </select>
            <input type="date" class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @foreach($activities as $activity)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex gap-4">
                    {{-- User Avatar --}}
                    <div class="flex-none">
                        <div class="w-10 h-10 rounded-full {{ $activity['color'] }} flex items-center justify-center text-white text-sm font-bold">
                            {{ $activity['initial'] }}
                        </div>
                    </div>

                    {{-- Activity Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900">{{ $activity['user'] }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    @php
                                        $iconPaths = [
                                            'inbox' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                                            'plus' => 'M12 4.5v15m7.5-7.5h-15',
                                            'edit' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
                                            'check' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'minus' => 'M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'user-plus' => 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z',
                                            'login' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
                                            'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25',
                                            'logout' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
                                        ];
                                        $iconPath = $iconPaths[$activity['icon']] ?? $iconPaths['edit'];
                                    @endphp
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                                    </svg>
                                    {{ $activity['action'] }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-400 whitespace-nowrap">{{ date('M d, Y h:i A', strtotime($activity['timestamp'])) }}</span>
                        </div>
                        <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $totalCount ?? 0 }} activities</p>
            <div class="flex items-center gap-2">
                @if($activities->onFirstPage())
                <button class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50" disabled>
                    Previous
                </button>
                @else
                <a href="{{ $activities->previousPageUrl() }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    Previous
                </a>
                @endif

                @foreach($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                    @if($page == $activities->currentPage())
                    <button class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 rounded-lg">{{ $page }}</button>
                    @else
                    <a href="{{ $url }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($activities->hasMorePages())
                <a href="{{ $activities->nextPageUrl() }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    Next
                </a>
                @else
                <button class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50" disabled>
                    Next
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
