<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} — TeleStore</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0">

        {{-- Brand --}}
        <div class="px-6 py-5 border-b border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-sm">✈️</div>
                <div>
                    <div class="font-bold text-sm">TeleStore</div>
                    <div class="text-xs text-gray-500 font-mono">Admin Panel</div>
                </div>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'icon' => '📊', 'label' => 'Dashboard'],
                    ['route' => 'admin.files',     'icon' => '📂', 'label' => 'Manajemen File'],
                    ['route' => 'admin.settings',  'icon' => '⚙️', 'label' => 'Pengaturan'],
                    ['route' => 'admin.users',     'icon' => '👥', 'label' => 'Pengguna'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all
                          {{ request()->routeIs($item['route']) 
                             ? 'bg-teal-500/10 text-teal-400 font-medium' 
                             : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <span>{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="pt-4 mt-4 border-t border-gray-800">
                <a href="{{ route('home') }}" target="_blank"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:text-gray-300 hover:bg-gray-800 transition-all">
                    <span>🌐</span> Lihat Halaman Publik
                </a>
            </div>
        </nav>

        {{-- User --}}
        <div class="px-4 py-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-xs text-gray-500 hover:text-red-400 py-1.5 px-3 rounded-md hover:bg-red-500/10 transition-colors text-left">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="h-14 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
            <h1 class="font-semibold text-base">{{ $title ?? 'Admin' }}</h1>
            <div class="flex items-center gap-2 text-xs text-gray-500 font-mono">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                Online
            </div>
        </header>

        {{-- Toast Notification --}}
        <div
            x-data="{ show: false, type: 'success', message: '' }"
            x-on:notify.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 3500)"
            class="fixed bottom-6 right-6 z-50"
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="px-4 py-3 rounded-xl shadow-xl border text-sm font-medium flex items-center gap-2"
                 :class="{
                     'bg-gray-900 border-teal-500 text-teal-300': type === 'success',
                     'bg-gray-900 border-red-500 text-red-300': type === 'error',
                     'bg-gray-900 border-blue-500 text-blue-300': type === 'info',
                 }">
                <span x-text="type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'"></span>
                <span x-text="message"></span>
            </div>
        </div>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
