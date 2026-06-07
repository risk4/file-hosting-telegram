<x-layouts.public title="Beranda">

@if(session('status'))
<div class="max-w-5xl mx-auto px-6 py-4">
    <div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-200 px-4 py-3 text-sm">
        {{ session('status') }}
    </div>
</div>
@endif

{{-- Hero --}}
<div class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 via-transparent to-blue-500/5 pointer-events-none"></div>
    <div class="max-w-5xl mx-auto px-6 py-20 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-teal-500/30 bg-teal-500/5 text-teal-400 text-xs font-mono mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
            Powered by Telegram Storage
        </div>
        <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight mb-6 bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
            {{ \App\Models\Setting::get('site_name', 'TeleStore') }}
        </h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            {{ \App\Models\Setting::get('site_description', 'Simpan dan bagikan file menggunakan Telegram sebagai backend storage.') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            @if(\App\Models\Setting::get('public_browse_enabled', true))
            <a href="{{ route('public.browse') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-400 text-black font-semibold rounded-xl transition-all hover:-translate-y-0.5 shadow-lg shadow-teal-500/20">
                📂 Browse File
            </a>
            @endif
            @if(\App\Models\Setting::get('public_upload_enabled', true))
            <a href="{{ route('public.upload') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 hover:bg-gray-700 border border-gray-700 font-semibold rounded-xl transition-all hover:-translate-y-0.5">
                ☁️ Upload File
            </a>
            @endif
        </div>
    </div>
</div>

{{-- Stats --}}
@php
    $totalFiles = \App\Models\TeleFile::public()->count();
    $totalSize  = \App\Models\TeleFile::public()->sum('size');
    $totalDl    = \App\Models\TeleFile::public()->sum('download_count');
    function fmtSize($b) {
        if ($b < 1024) return $b . ' B';
        if ($b < 1048576) return round($b/1024,1) . ' KB';
        if ($b < 1073741824) return round($b/1048576,1) . ' MB';
        return round($b/1073741824,2) . ' GB';
    }
@endphp
<div class="max-w-5xl mx-auto px-6 pb-16">
    <div class="grid grid-cols-3 gap-4 mb-16">
        @foreach([
            ['label' => 'Total File', 'value' => number_format($totalFiles), 'icon' => '📁'],
            ['label' => 'Total Ukuran', 'value' => fmtSize($totalSize), 'icon' => '💾'],
            ['label' => 'Total Download', 'value' => number_format($totalDl), 'icon' => '⬇️'],
        ] as $stat)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center">
            <div class="text-3xl mb-2">{{ $stat['icon'] }}</div>
            <div class="text-2xl font-bold font-mono text-teal-400">{{ $stat['value'] }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Recent files --}}
    @if(\App\Models\Setting::get('public_browse_enabled', true) && $totalFiles > 0)
    <div>
        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
            🕐 <span>File Terbaru</span>
        </h2>
        <div class="grid gap-3">
            @foreach(\App\Models\TeleFile::public()->latest()->take(6)->get() as $file)
            <div class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl p-4 flex items-center gap-4 transition-colors group">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0
                    {{ match($file->type) {
                        'image' => 'bg-emerald-500/10',
                        'video' => 'bg-orange-500/10',
                        'doc'   => 'bg-blue-500/10',
                        'note'  => 'bg-yellow-500/10',
                        default => 'bg-gray-700/50'
                    } }}">
                    {{ $file->icon }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ $file->name }}</div>
                    <div class="text-xs text-gray-500 font-mono mt-0.5 flex gap-3">
                        <span>{{ $file->formatted_size }}</span>
                        <span>{{ $file->created_at->diffForHumans() }}</span>
                        <span class="text-{{ $file->type_color }}-400">{{ strtoupper($file->type) }}</span>
                    </div>
                </div>
                <a href="{{ route('file.download', $file->uuid) }}"
                   class="opacity-0 group-hover:opacity-100 px-3 py-1.5 text-xs font-mono rounded-lg border border-gray-700 hover:border-teal-500 hover:text-teal-400 transition-all">
                    ⬇️ Unduh
                </a>
            </div>
            @endforeach
        </div>
        @if(\App\Models\Setting::get('public_browse_enabled', true))
        <div class="text-center mt-6">
            <a href="{{ route('public.browse') }}" class="text-sm text-teal-400 hover:text-teal-300 font-mono">
                Lihat semua file →
            </a>
        </div>
        @endif
    </div>
    @endif
</div>

</x-layouts.public>
