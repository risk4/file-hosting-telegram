<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gray-800 flex items-center justify-center text-2xl">
                @switch($file->type)
                    @case('image') 🖼️ @break
                    @case('video') 🎬 @break
                    @case('doc') 📄 @break
                    @case('note') 📝 @break
                    @default 📦
                @endswitch
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold truncate">{{ $file->original_name }}</h1>
                <p class="text-sm text-gray-400 font-mono">
                    {{ \App\Models\Setting::get('site_name', 'TeleStore') }}
                </p>
            </div>
        </div>

        {{-- File Info --}}
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Ukuran</p>
                <p class="font-mono mt-1">
                    @php
                        $size = $file->size;
                        if ($size >= 1073741824) {
                            echo round($size / 1073741824, 2) . ' GB';
                        } elseif ($size >= 1048576) {
                            echo round($size / 1048576, 1) . ' MB';
                        } elseif ($size >= 1024) {
                            echo round($size / 1024) . ' KB';
                        } else {
                            echo $size . ' B';
                        }
                    @endphp
                </p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Tipe</p>
                <p class="font-mono mt-1">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION)) ?: $file->type }}</p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Kategori</p>
                <p class="font-mono mt-1 capitalize">{{ $file->category }}</p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Diunduh</p>
                <p class="font-mono mt-1">{{ $file->download_count ?? 0 }} kali</p>
            </div>
        </div>

        @if($file->description)
        <div class="bg-gray-800/50 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-500 font-mono uppercase tracking-wider mb-1">Deskripsi</p>
            <p class="text-sm text-gray-300">{{ $file->description }}</p>
        </div>
        @endif

        {{-- Download Button --}}
        <a href="{{ route('file.download', $file->uuid) }}"
           class="flex items-center justify-center gap-3 w-full py-4 bg-teal-500 hover:bg-teal-400 text-black font-bold rounded-xl transition-all text-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download File
        </a>

        {{-- Preview for images/documents --}}
        @if(in_array($file->type, ['image', 'doc']) && in_array(strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp','pdf']))
        <div class="text-center">
            <a href="{{ route('file.preview', $file->uuid) }}" target="_blank"
               class="text-sm text-teal-400 hover:text-teal-300 font-mono">
                👁️ Lihat / Preview
            </a>
        </div>
        @endif
    </div>
</div>