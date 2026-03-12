<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Canson's School & Office Supplies</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/pages/login.css', 'resources/js/app.js', 'resources/js/pages/login.js'])
</head>
<body class="min-h-screen bg-gray-50">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- ==================== LEFT PANEL — Brand & Hero ==================== --}}
        <div class="login-left hidden lg:flex lg:w-[58%] xl:w-[60%] flex-col justify-between p-10 xl:p-14 relative z-10">

            {{-- Top: Logo --}}
            <div class="fade-in-up">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 6h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM10 4h4v2h-4V4zm10 16H4V8h16v12z"/>
                            <path d="M11 10h2v3h3v2h-3v3h-2v-3H8v-2h3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">Canson's</h2>
                        <p class="text-emerald-400 text-xs font-medium tracking-wide">School & Office Supplies</p>
                    </div>
                </div>
            </div>

            {{-- Center: Hero Content --}}
            <div class="flex-1 flex flex-col justify-center max-w-xl -mt-8">
                <div class="fade-in-up fade-in-up-delay-1">
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Canson's Order Tracking and Workforce Tasking
                        <span class="block mt-1 text-emerald-400">System</span>
                    </h1>
                </div>

                <div class="fade-in-up fade-in-up-delay-2">
                    <p class="mt-5 text-gray-400 text-base leading-relaxed max-w-md">
                        Manage orders, workforce, inventory, and dispatch from a single, unified platform.
                    </p>
                </div>

                {{-- Feature Pills --}}
                <div class="fade-in-up fade-in-up-delay-3 flex flex-wrap gap-2.5 mt-8">
                    <span class="stat-pill px-4 py-2 rounded-full text-emerald-300 text-sm font-medium">
                        Orders
                    </span>
                    <span class="stat-pill px-4 py-2 rounded-full text-emerald-300 text-sm font-medium">
                        Workforce
                    </span>
                    <span class="stat-pill px-4 py-2 rounded-full text-emerald-300 text-sm font-medium">
                        Inventory
                    </span>
                    <span class="stat-pill px-4 py-2 rounded-full text-emerald-300 text-sm font-medium">
                        Dispatch
                    </span>
                </div>

                {{-- Feature Cards --}}
                <div class="fade-in-up fade-in-up-delay-4 grid grid-cols-2 gap-3 mt-10">
                    <div class="feature-card rounded-xl p-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                            </svg>
                        </div>
                        <h3 class="text-white text-sm font-semibold">Real-time Tracking</h3>
                        <p class="text-gray-500 text-xs mt-1 leading-relaxed">Monitor orders and deliveries live</p>
                    </div>
                    <div class="feature-card rounded-xl p-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-white text-sm font-semibold">Team Management</h3>
                        <p class="text-gray-500 text-xs mt-1 leading-relaxed">Assign tasks to workers & drivers</p>
                    </div>
                    <div class="feature-card rounded-xl p-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </div>
                        <h3 class="text-white text-sm font-semibold">Stock Control</h3>
                        <p class="text-gray-500 text-xs mt-1 leading-relaxed">Track inventory in real-time</p>
                    </div>
                    <div class="feature-card rounded-xl p-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                            </svg>
                        </div>
                        <h3 class="text-white text-sm font-semibold">Fast Dispatch</h3>
                        <p class="text-gray-500 text-xs mt-1 leading-relaxed">Streamlined delivery workflow</p>
                    </div>
                </div>
            </div>

            {{-- Bottom: Decorative --}}
            <div class="fade-in-up">
                <div class="flex items-center gap-3 text-gray-600 text-xs">
                    <div class="w-8 h-[2px] bg-emerald-500/30 rounded-full"></div>
                    <span>Empowering business operations since 2026</span>
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT PANEL — Login Form ==================== --}}
        <div class="flex-1 flex flex-col items-center justify-center px-6 sm:px-10 lg:px-14 xl:px-20 py-10 bg-white relative">

            <div class="w-full max-w-sm">

                {{-- Mobile logo (hidden on desktop) --}}
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 6h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM10 4h4v2h-4V4zm10 16H4V8h16v12z"/>
                            <path d="M11 10h2v3h3v2h-3v3h-2v-3H8v-2h3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-gray-900 font-bold text-base leading-tight">Canson's</h2>
                        <p class="text-emerald-600 text-xs font-medium">School & Office Supplies</p>
                    </div>
                </div>

                {{-- Heading --}}
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Welcome back</h1>
                    <p class="text-gray-500 mt-1.5 text-sm">Sign in to your account to continue</p>
                </div>

                {{-- Error Messages --}}
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span class="text-sm font-medium text-red-700">Invalid credentials</span>
                        </div>
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-600 ml-6">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                                   placeholder="Enter your username"
                                   class="input-field w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-900
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                          placeholder:text-gray-400 bg-gray-50/50 hover:bg-white hover:border-gray-300">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" required
                                   placeholder="Enter your password"
                                   class="input-field w-full pl-11 pr-12 py-3 border border-gray-200 rounded-xl text-sm text-gray-900
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                          placeholder:text-gray-400 bg-gray-50/50 hover:bg-white hover:border-gray-300">
                            {{-- Toggle Password Visibility --}}
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                                <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="remember"
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Remember me</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="btn-signin w-full py-3.5 px-4 text-white rounded-xl text-sm font-semibold
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 mt-2">
                        Sign In
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <p class="absolute bottom-6 text-center text-xs text-gray-400">&copy; {{ date('Y') }} Canson's School & Office Supplies. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
