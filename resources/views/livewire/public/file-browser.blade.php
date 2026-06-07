<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight mb-2">📂 Browse File</h1>
        <p class="text-gray-400 font-mono text-sm">{{ $counts['all'] }} file tersedia</p>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">🔍</span>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari file..."
                class="w-full bg-gray-900 border border-gray-800 rounded-xl pl-9 pr-4 py-2.5 text-sm font-mono text-gray-100 placeholder-gray-600 focus:outline-none focus:border-teal-500 transition-colors"
            />
        </div>
        <select wire:model.live="sort"
                class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-gray-300 focus:outline-none focus:border-teal-500">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="name">Nama A-Z</option>
            <option value="size_desc">Ukuran Terbesar</option>
            <option value="downloads">Paling Banyak Diunduh</option>
        </select>
    </div>

    {{-- Type Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach([
            ['value' => 'all',   'label' => 'Semua',    'icon' => '📂'],
            ['value' => 'image', 'label' => 'Gambar',   'icon' => '🖼️'],
            ['value' => 'video', 'label' => 'Video',    'icon' => '🎬'],
            ['value' => 'doc',   'label' => 'Dokumen',  'icon' => '📄'],
            ['value' => 'note',  'label' => 'Catatan',  'icon' => '📝'],
            ['value' => 'other', 'label' => 'Lainnya',  'icon' => '📦'],
        ] as $tab)
        <button wire:click="$set('type', '{{ $tab['value'] }}')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono transition-all
                       {{ $type === $tab['value']
                          ? 'bg-teal-500/15 text-teal-400 border border-teal-500/40'
                          : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:border-gray-600' }}">
            {{ $tab['icon'] }} {{ $tab['label'] }}
            <span class="opacity-60">({{ $counts[$tab['value']] }})</span>
        </button>
        @endforeach
    </div>

    {{-- Loading --}}
    <div wire:loading class="text-center py-8 text-gray-500 font-mono text-sm">⏳ Memuat...</div>

    {{-- File Grid --}}
    <div wire:loading.remove>
        @if($files->isEmpty())
            <div class="text-center py-20">
                <div class="text-4xl mb-3">📭</div>
                <p class="text-gray-500 font-mono text-sm">Tidak ada file ditemukan</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($files as $file)
                <div class="group bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-2xl p-4 transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-black/20">
                    {{-- Type badge --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl
                            {{ match($file->type) {
                                'image' => 'bg-emerald-500/10',
                                'video' => 'bg-orange-500/10',
                                'doc'   => 'bg-blue-500/10',
                                'note'  => 'bg-yellow-500/10',
                                default => 'bg-gray-800'
                            } }}">
                            {{ $file->icon }}
                        </div>
                        <span class="text-xs font-mono px-2 py-0.5 rounded-md
                            {{ match($file->type) {
                                'image' => 'bg-emerald-500/10 text-emerald-400',
                                'video' => 'bg-orange-500/10 text-orange-400',
                                'doc'   => 'bg-blue-500/10 text-blue-400',
                                'note'  => 'bg-yellow-500/10 text-yellow-400',
                                default => 'bg-gray-800 text-gray-400'
                            } }}">
                            {{ strtoupper($file->type) }}
                        </span>
                    </div>

                    {{-- Name --}}
                    <h3 class="font-semibold text-sm mb-1 line-clamp-2 leading-snug">{{ $file->name }}</h3>
                    @if($file->description)
                        <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ $file->description }}</p>
                    @endif

                    {{-- Meta --}}
                    <div class="flex items-center gap-2 text-xs font-mono text-gray-600 mb-4 flex-wrap">
                        <span>{{ $file->formatted_size }}</span>
                        <span>·</span>
                        <span>{{ $file->created_at->diffForHumans() }}</span>
                        @if($file->category !== 'uncategorized')
                        <span>·</span>
                        <span class="text-gray-500">{{ $file->category }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        @if($file->type === 'note')
                            <button wire:click="openPreview({{ $file->id }})"
                                    class="flex-1 py-2 text-xs font-mono rounded-lg bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20 transition-colors">
                                👁 Lihat
                            </button>
                        @else
                            @if(in_array($file->type, ['image']) || str_contains($file->mime_type ?? '', 'pdf'))
                            <button wire:click="openPreview({{ $file->id }})"
                                    class="py-2 px-3 text-xs font-mono rounded-lg bg-gray-800 text-gray-400 hover:text-white transition-colors">
                                👁
                            </button>
                            @endif
                            <a href="{{ route('file.download', $file->uuid) }}"
                               class="flex-1 py-2 text-xs font-mono rounded-lg bg-teal-500/10 text-teal-400 hover:bg-teal-500/20 transition-colors text-center">
                                ⬇️ Unduh
                            </a>
                        @endif
                    </div>

                    {{-- Download count --}}
                    @if($file->download_count > 0)
                    <div class="mt-2 text-xs text-gray-600 font-mono text-right">
                        {{ $file->download_count }}x diunduh
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $files->links() }}
        @endif
    </div>

    {{-- Preview Modal --}}
    @if($showPreview && $previewFile)
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         wire:click.self="closePreview">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold">{{ $previewFile->name }}</h3>
                <button wire:click="closePreview" class="text-gray-500 hover:text-white text-xl leading-none">✕</button>
            </div>
            <div class="flex-1 overflow-auto p-6">
                @if($previewFile->type === 'note')
                    <pre class="bg-gray-950 rounded-xl p-4 text-sm font-mono text-teal-300 whitespace-pre-wrap break-words">{{ $previewFile->content }}</pre>
                @elseif($previewFile->type === 'image')
                    <img src="{{ route('file.preview', $previewFile->uuid) }}" alt="{{ $previewFile->name }}" class="max-w-full rounded-xl mx-auto">
                @elseif(str_contains($previewFile->mime_type ?? '', 'pdf'))
                    <iframe src="{{ route('file.preview', $previewFile->uuid) }}" class="w-full h-96 rounded-xl"></iframe>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-gray-800 flex justify-between items-center">
                <span class="text-xs font-mono text-gray-500">{{ $previewFile->formatted_size }}</span>
                @if($previewFile->type !== 'note')
                <a href="{{ route('file.download', $previewFile->uuid) }}"
                   class="px-4 py-2 bg-teal-500 text-black text-sm font-semibold rounded-lg hover:bg-teal-400 transition-colors">
                    ⬇️ Unduh
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
