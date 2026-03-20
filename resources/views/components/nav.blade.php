<div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">
            @if(auth()->user()->isEmployee())
                Worker Dashboard
            @else
                Manager
            @endif
        </span></h1>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-600 overflow-hidden flex items-center justify-center text-white text-sm font-bold">
                @if(!empty(auth()->user()->profile_image))
                    <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    {{ auth()->user()->initial }}
                @endif
            </div>
        </div>
    </div>
