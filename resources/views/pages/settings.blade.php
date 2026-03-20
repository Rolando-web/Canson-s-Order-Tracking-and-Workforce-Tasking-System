@extends('partials.app', ['title' => 'Settings - Canson', 'activePage' => 'settings'])

@push('styles')
    @vite('resources/css/pages/settings.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/settings.js')
@endpush

@section('content')
<div class="settings-page">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
        <p class="text-gray-500 mt-1">Manage your account and application preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Settings Content --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Profile Settings</h3>

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
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
                    <div class="w-14 h-12 md:w-18 md:h-18 sm:w-15 sm:h-15 rounded-full bg-emerald-600 overflow-hidden flex items-center justify-center text-white text-2xl font-bold" id="avatarPreviewWrap">
                        @if(!empty($user->profile_image))
                            <img id="avatarPreviewImage" src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Avatar" class="w-full h-full object-cover">
                        @else
                            <span id="avatarPreviewInitials">{{ strtoupper(substr($firstName ?? 'A', 0, 1) . substr($lastName ?? 'D', 0, 1)) }}</span>
                            <img id="avatarPreviewImage" src="" alt="Profile Avatar" class="w-full h-full object-cover hidden">
                        @endif
                    </div>
                    <div>
                        <input type="file" id="profileImageInput" name="profile_image" accept="image/png,image/jpeg,image/jpg,image/gif" class="hidden">
                        <button type="button" id="changeAvatarBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Change Avatar</button>
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
                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Save Changes</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
