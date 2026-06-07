<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'TeleStore') }}</title>
    <meta name="description" content="{{ \App\Models\Setting::get('site_description', 'Cloud storage berbasis Telegram') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-sm">✈️</div>
                <span class="font-bold text-lg tracking-tight">
                    {{ \App\Models\Setting::get('site_name', 'TeleStore') }}
                </span>
            </a>
            <div class="flex items-center gap-2">
                @if(\App\Models\Setting::get('public_browse_enabled', true))
                <a href="{{ route('public.browse') }}"
                   class="px-4 py-2 text-sm rounded-lg {{ request()->routeIs('public.browse') ? 'bg-teal-500/10 text-teal-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} transition-colors">
                    📂 Browse
                </a>
                @endif
                @if(\App\Models\Setting::get('public_upload_enabled', true))
                <a href="{{ route('public.upload') }}"
                   class="px-4 py-2 text-sm rounded-lg {{ request()->routeIs('public.upload') ? 'bg-teal-500/10 text-teal-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} transition-colors">
                    ☁️ Upload
                </a>
                @endif
                <a href="{{ route('admin.dashboard') }}"
                   class="ml-2 px-3 py-1.5 text-xs font-mono rounded-md border border-gray-700 text-gray-400 hover:border-teal-500 hover:text-teal-400 transition-colors">
                    Admin →
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- Main --}}
<main class="flex-1">
    {{ $slot }}
</main>

{{-- Footer --}}
<footer class="border-t border-gray-800 py-8 mt-12">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <p class="text-gray-600 text-sm font-mono">
            {{ \App\Models\Setting::get('site_name', 'TeleStore') }} — powered by Telegram
        </p>
    </div>
</footer>

@livewireScripts
</body>
</html>
