<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total File',      'value' => number_format($stats['total_files']),     'icon' => '📁', 'color' => 'teal'],
            ['label' => 'Total Ukuran',    'value' => $this->formattedSize($stats['total_size']), 'icon' => '💾', 'color' => 'blue'],
            ['label' => 'Total Download',  'value' => number_format($stats['total_downloads']), 'icon' => '⬇️', 'color' => 'purple'],
            ['label' => 'Upload Hari Ini', 'value' => $stats['today_uploads'],                  'icon' => '🕐', 'color' => 'orange'],
        ] as $stat)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="text-2xl">{{ $stat['icon'] }}</div>
                <span class="text-xs font-mono text-gray-600">{{ $stat['label'] }}</span>
            </div>
            <div class="text-2xl font-bold font-mono text-{{ $stat['color'] }}-400">{{ $stat['value'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- File type breakdown --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">📊 Breakdown Tipe File</h3>
            <div class="space-y-3">
                @foreach([
                    ['label' => 'Gambar',  'key' => 'images', 'icon' => '🖼️', 'color' => 'emerald'],
                    ['label' => 'Video',   'key' => 'videos', 'icon' => '🎬', 'color' => 'orange'],
                    ['label' => 'Dokumen', 'key' => 'docs',   'icon' => '📄', 'color' => 'blue'],
                    ['label' => 'Catatan', 'key' => 'total_notes', 'icon' => '📝', 'color' => 'yellow'],
                ] as $type)
                @php $total = max($stats['total_files'], 1); $pct = round($stats[$type['key']] / $total * 100); @endphp
                <div>
                    <div class="flex justify-between text-xs font-mono text-gray-400 mb-1">
                        <span>{{ $type['icon'] }} {{ $type['label'] }}</span>
                        <span>{{ $stats[$type['key']] }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $type['color'] }}-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sidecar status --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                🔌 Status Sidecar (Node.js)
                <button wire:click="checkSidecar" class="ml-auto text-xs text-gray-500 hover:text-teal-400 font-mono">↻ Refresh</button>
            </h3>
            @if($sidecarStatus['connected'] ?? false)
                <div class="flex items-center gap-2 text-teal-400 font-mono text-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    Terhubung
                </div>
                @if(isset($sidecarStatus['user']))
                <div class="text-xs text-gray-400 font-mono space-y-1">
                    <div>👤 {{ $sidecarStatus['user']['firstName'] ?? '' }} {{ $sidecarStatus['user']['lastName'] ?? '' }}</div>
                    @if($sidecarStatus['user']['username'] ?? '')
                    <div>🔗 &#64;{{ $sidecarStatus['user']['username'] }}</div>
                    @endif
                </div>
                @endif
            @else
                <div class="flex items-center gap-2 text-red-400 font-mono text-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    Tidak terhubung
                </div>
                @if(isset($sidecarStatus['error']))
                <p class="text-xs text-gray-500 font-mono">{{ $sidecarStatus['error'] }}</p>
                @endif
                <a href="{{ route('admin.settings') }}" class="mt-3 inline-block text-xs text-teal-400 hover:underline font-mono">
                    → Konfigurasi di Pengaturan
                </a>
            @endif
        </div>

        {{-- Quick stats --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4">📈 Statistik Lain</h3>
            <div class="space-y-3 text-sm font-mono">
                @foreach([
                    ['label' => 'Upload minggu ini',   'value' => $stats['week_uploads']],
                    ['label' => 'Upload oleh guest',   'value' => $stats['guest_uploads']],
                    ['label' => 'Catatan tersimpan',   'value' => $stats['total_notes']],
                ] as $s)
                <div class="flex justify-between text-gray-400">
                    <span>{{ $s['label'] }}</span>
                    <span class="text-white font-bold">{{ $s['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Files --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <h3 class="font-semibold text-sm">🕐 File Terbaru</h3>
            <a href="{{ route('admin.files') }}" class="text-xs text-teal-400 hover:underline font-mono">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-800">
            @foreach($recentFiles as $file)
            <div class="flex items-center gap-4 px-6 py-3 hover:bg-gray-800/50 transition-colors">
                <span class="text-xl">{{ $file['type'] === 'image' ? '🖼️' : ($file['type'] === 'video' ? '🎬' : ($file['type'] === 'note' ? '📝' : '📄')) }}</span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">{{ $file['name'] }}</div>
                    <div class="text-xs text-gray-500 font-mono">{{ $file['uploaded_by'] }} · {{ \Carbon\Carbon::parse($file['created_at'])->diffForHumans() }}</div>
                </div>
                <span class="text-xs font-mono text-gray-500">
                    @php
                        $b = $file['size'];
                        echo $b < 1048576 ? round($b/1024,1).' KB' : round($b/1048576,1).' MB';
                    @endphp
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
