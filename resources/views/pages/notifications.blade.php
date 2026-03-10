@extends('partials.app', ['title' => 'Notifications - Canson', 'activePage' => 'notifications'])

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Notifications</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="flex items-center gap-2">
                @if(auth()->user()->isEmployee())
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700">Worker</span>
                @elseif(auth()->user()->isAdmin())
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">Manager</span>
                @elseif(auth()->user()->isSuperAdmin())
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-700">Boss</span>
                @endif
                <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">
                    {{ auth()->user()->initial }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Your Notifications</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                @if($unreadCount > 0)
                    You have <span class="font-semibold text-emerald-600">{{ $unreadCount }}</span> unread notification{{ $unreadCount > 1 ? 's' : '' }}
                @else
                    All caught up!
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
            <button onclick="markAllAsRead()" class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                Mark all as read
            </button>
        @endif
    </div>

    {{-- Notification List --}}
    @if($notifications->count() > 0)
        <div class="space-y-3" id="notificationList">
            @foreach($notifications as $notif)
                <div id="notif-{{ $notif->id }}"
                     class="notification-card group bg-white rounded-xl border {{ $notif->is_read ? 'border-gray-200' : 'border-emerald-200 bg-emerald-50/30' }} p-4 flex items-start gap-4 transition-all duration-200 hover:shadow-md">

                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                        @switch($notif->type)
                            @case('new_order')
                                bg-blue-100
                                @break
                            @case('work_assigned')
                                bg-emerald-100
                                @break
                            @case('delivery_assigned')
                                bg-orange-100
                                @break
                            @default
                                bg-indigo-700
                        @endswitch
                    ">
                        @switch($notif->type)
                            @case('new_order')
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                                </svg>
                                @break
                            @case('work_assigned')
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.65 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/>
                                </svg>
                                @break
                            @case('delivery_assigned')
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                                </svg>
                                @break
                            @default
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                </svg>
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 {{ !$notif->is_read ? 'text-gray-900' : '' }}">
                                    {{ $notif->title }}
                                </p>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $notif->message }}</p>
                            </div>
                            @if(!$notif->is_read)
                                <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5"></span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>

                            @if(!$notif->is_read)
                                <button onclick="markAsRead({{ $notif->id }})" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium transition-colors">
                                    Mark as read
                                </button>
                            @endif

                            {{-- Quick action links based on type --}}
                            @if($notif->type === 'new_order')
                                <a href="{{ route('assignments') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                    Go to Assignments
                                </a>
                            @elseif($notif->type === 'work_assigned' || $notif->type === 'delivery_assigned')
                                <a href="{{ route('assignments') }}" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium transition-colors">
                                    View My Work
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-600">No Notifications</h3>
            <p class="text-sm text-gray-400 mt-1">You're all caught up! Check back later.</p>
        </div>
    @endif
</div>

{{-- Toast --}}
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden transform transition-all duration-300 translate-y-2 opacity-0">
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-white text-sm font-medium" id="toastInner">
        <span id="toastMsg"></span>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/notifications.js')
@endpush
