@extends('partials.app', ['title' => 'Settings - Canson', 'activePage' => 'settings'])

@push('styles')
    @vite('resources/css/pages/settings.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/settings.js')
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
<div class="settings-page">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
        <p class="text-gray-500 mt-1">Manage your account and application preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Settings Navigation --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <nav class="space-y-1">
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-600 text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Profile
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    Notifications
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Security
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072"/></svg>
                    Appearance
                </a>
            </nav>
        </div>

        {{-- Settings Content --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Profile Settings</h3>

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

            <div class="space-y-6">
                {{-- Avatar --}}
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full bg-emerald-600 flex items-center justify-center text-white text-2xl font-bold">{{ strtoupper(substr($firstName ?? 'A', 0, 1) . substr($lastName ?? 'D', 0, 1)) }}</div>
                    <div>
                        <button type="button" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Change Avatar</button>
                        <p class="text-xs text-gray-400 mt-2">JPG, PNG or GIF. Max size of 2MB.</p>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Form Fields --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $firstName ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $lastName ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role ?? 'employee')) }}" disabled class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>

                <hr class="border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700">Change Password</h4>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                        <input type="password" name="new_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="new_password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Save Changes</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
