<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10"
     x-data="{ startTime: null, elapsed: '0:00', timerInterval: null }"
     x-on:livewire-upload-start="startTime = Date.now(); elapsed = '0:00'; timerInterval = setInterval(() => { let s = Math.floor((Date.now() - startTime) / 1000); let m = Math.floor(s / 60); elapsed = m + ':' + String(s % 60).padStart(2, '0'); }, 1000)"
     x-on:livewire-upload-finish="clearInterval(timerInterval)"
     x-on:livewire-upload-error="clearInterval(timerInterval)">

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight mb-2">☁️ Upload File</h1>
        <p class="text-gray-400 font-mono text-sm">
            Maks {{ \App\Models\Setting::get('max_upload_mb', 2048) }}MB per file
        </p>
    </div>

    {{-- Tab --}}
    <div class="flex gap-2 mb-6 bg-gray-900 border border-gray-800 rounded-xl p-1 w-fit">
        <button wire:click="$set('activeTab', 'file')"
                class="px-5 py-2 text-sm font-medium rounded-lg transition-all
                       {{ $activeTab === 'file' ? 'bg-teal-500/15 text-teal-400' : 'text-gray-400 hover:text-white' }}">
            📁 File
        </button>
        <button wire:click="$set('activeTab', 'note')"
                class="px-5 py-2 text-sm font-medium rounded-lg transition-all
                       {{ $activeTab === 'note' ? 'bg-teal-500/15 text-teal-400' : 'text-gray-400 hover:text-white' }}">
            📝 Catatan
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- PROCESSING PROGRESS (Server → Telegram)            --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($isProcessing)
    <div wire:poll.2s="processNextFile"
         x-data="processingTimer()"
         x-init="$watch('$wire.isProcessing', val => { if(val) startTimer(); else stopTimer(); }); if($wire.isProcessing) startTimer();"
         class="mb-6 bg-gray-900 border border-teal-500/30 rounded-2xl p-6 space-y-4">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="relative flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-teal-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-teal-300">Mengirim ke Telegram</h3>
                <p class="text-xs text-gray-400 font-mono">
                    File {{ $currentFileIndex + 1 }} dari {{ $totalToProcess }}
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono text-gray-400" x-text="elapsed">0:00</span>
                <p class="text-xs text-gray-500">elapsed</p>
            </div>
        </div>

        {{-- Progress bar --}}
        @php
            $progressPercent = $totalToProcess > 0 ? round(($currentFileIndex / $totalToProcess) * 100) : 0;
        @endphp
        <div class="space-y-1">
            <div class="w-full h-2.5 bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-teal-500 to-teal-400 rounded-full transition-all duration-500 ease-out"
                     style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="flex justify-between text-xs font-mono text-gray-500">
                <span>{{ $progressPercent }}%</span>
                <span>{{ $currentFileIndex }}/{{ $totalToProcess }} selesai</span>
            </div>
        </div>

        {{-- Current file --}}
        @if($processingFileName)
        <div class="flex items-center gap-2 px-3 py-2 bg-gray-800/50 rounded-lg border border-gray-700/50">
            <span class="text-teal-400 animate-pulse">⏳</span>
            <span class="text-xs font-mono text-gray-300 truncate">{{ $processingFileName }}</span>
        </div>
        @endif

        {{-- Completed files list --}}
        @if(count($uploadResults) > 0)
        <div class="space-y-1">
            @foreach($uploadResults as $result)
            <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-mono
                        {{ $result['status'] === 'success' ? 'text-teal-400' : 'text-red-400' }}">
                <span>{{ $result['status'] === 'success' ? '✅' : '❌' }}</span>
                <span class="truncate">{{ $result['name'] }}</span>
                @if(isset($result['message']))
                    <span class="text-gray-500 truncate ml-auto">— {{ $result['message'] }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- COMPLETED RESULTS                                  --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if(!$isProcessing && count($uploadResults) > 0)
    <div class="mb-6 space-y-2">
        @foreach($uploadResults as $result)
        <div x-data="{ copied: false }"
             class="flex flex-col gap-3 px-4 py-3 rounded-xl border text-sm
                    {{ $result['status'] === 'success' ? 'bg-teal-500/5 border-teal-500/30 text-teal-300' : 'bg-red-500/5 border-red-500/30 text-red-300' }}">
            <div class="flex items-center gap-3">
                <span>{{ $result['status'] === 'success' ? '✅' : '❌' }}</span>
                <span class="font-mono">{{ $result['name'] }}</span>
                @if(isset($result['message']))
                    <span class="text-xs opacity-70">— {{ $result['message'] }}</span>
                @endif
            </div>
            @if($result['status'] === 'success' && isset($result['url']))
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <a href="{{ $result['url'] }}" target="_blank" rel="noopener noreferrer"
                   class="truncate text-xs text-teal-200 hover:text-teal-100 font-mono">
                    {{ $result['url'] }}
                </a>
                <button type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $result['url'] }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="px-3 py-1 rounded-lg bg-gray-800 border border-gray-700 text-xs text-gray-200 hover:bg-gray-700 transition-all">
                    <span x-text="copied ? 'Tersalin!' : 'Salin Link'"></span>
                </button>
            </div>
            @endif
        </div>
        @endforeach
        <button wire:click="clearResults" class="text-xs text-gray-500 hover:text-gray-400 font-mono">✕ Tutup</button>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- FILE TAB                                           --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($activeTab === 'file')
    <div x-data="{ browserProgress: 0, uploading: false, dragging: false }"
         x-on:livewire-upload-start="uploading = true; browserProgress = 0"
         x-on:livewire-upload-progress="browserProgress = $event.detail.progress"
         x-on:livewire-upload-finish="browserProgress = 100; setTimeout(() => uploading = false, 250)"
         x-on:livewire-upload-error="uploading = false"
         class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

        {{-- Drop zone --}}
        <div x-on:dragover.prevent="dragging = true"
             x-on:dragleave="dragging = false"
             x-on:drop.prevent="dragging = false"
             :class="dragging ? 'border-teal-500 bg-teal-500/5' : 'border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-all cursor-pointer"
             onclick="document.getElementById('fileInput').click()">
            <input type="file" id="fileInput" wire:model="files" multiple class="hidden">
            <div class="text-4xl mb-3">🗂️</div>
            <p class="font-semibold mb-1">Drag & drop atau klik untuk memilih</p>
            <p class="text-sm text-gray-500 font-mono">Hingga {{ \App\Models\Setting::get('max_upload_mb', 2048) }}MB per file</p>
        </div>

        {{-- ═══ Browser → Server upload progress ═══ --}}
        <template x-if="uploading">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400 animate-spin flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs text-gray-400 font-mono">Mengupload ke server...</span>
                </div>
                <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-teal-500 to-teal-400 transition-all duration-200 rounded-full"
                         :style="`width: ${browserProgress}%`"></div>
                </div>
                <div class="flex justify-between text-xs font-mono text-gray-500">
                    <span x-text="Math.round(browserProgress) + '%'">0%</span>
                    <span x-text="$wire.isProcessing ? 'Memproses...' : 'Selesai'"></span>
                </div>
            </div>
        </template>

        {{-- Selected files preview --}}
        @if(count($files) > 0)
        <div class="space-y-2">
            @foreach($files as $i => $file)
            <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-3 py-2 text-sm">
                <span class="text-gray-400">📎</span>
                <span class="flex-1 font-mono text-xs truncate">{{ $file->getClientOriginalName() }}</span>
                <span class="text-xs text-gray-500 font-mono">{{ round($file->getSize() / 1048576, 1) }} MB</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Error --}}
        @error('files.*') <p class="text-red-400 text-xs font-mono">{{ $message }}</p> @enderror

        {{-- Options --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Kategori</label>
                <input wire:model="category" type="text" list="category-list"
                       placeholder="uncategorized"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono text-gray-200 focus:outline-none focus:border-teal-500">
                <datalist id="category-list">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Deskripsi</label>
                <input wire:model="description" type="text"
                       placeholder="Opsional..."
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono text-gray-200 focus:outline-none focus:border-teal-500">
            </div>
        </div>

        <button wire:click="uploadFiles"
                wire:loading.attr="disabled"
                wire:target="uploadFiles"
                :disabled="$wire.isProcessing"
                class="w-full py-3 bg-teal-500 hover:bg-teal-400 disabled:opacity-50 text-black font-bold rounded-xl transition-all">
            <span wire:loading.remove wire:target="uploadFiles" x-show="!$wire.isProcessing">☁️ Upload ke Telegram</span>
            <span wire:loading wire:target="uploadFiles">⏳ Mengupload...</span>
        </button>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- NOTE TAB                                          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($activeTab === 'note')
    <div x-data="{ saving: false }"
         class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
        <div>
            <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Judul Catatan</label>
            <input wire:model="noteLabel" type="text" placeholder="Nama catatan (opsional)"
                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono text-gray-200 focus:outline-none focus:border-teal-500">
        </div>
        <div>
            <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Isi Catatan</label>
            <textarea wire:model="noteContent" rows="8"
                      placeholder="Tulis catatan, kode, link, password..."
                      class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm font-mono text-gray-200 focus:outline-none focus:border-teal-500 resize-y"></textarea>
            @error('noteContent') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Kategori</label>
            <input wire:model="category" type="text" list="category-list-note"
                   placeholder="uncategorized"
                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono text-gray-200 focus:outline-none focus:border-teal-500">
            <datalist id="category-list-note">
                @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
            </datalist>
        </div>
        <button wire:click="saveNote"
                wire:loading.attr="disabled"
                wire:target="saveNote"
                x-on:click="saving = true"
                class="w-full py-3 bg-teal-500 hover:bg-teal-400 disabled:opacity-50 text-black font-bold rounded-xl transition-all">
            <span wire:loading.remove wire:target="saveNote" x-show="!saving">💾 Simpan Catatan</span>
            <span wire:loading wire:target="saveNote">⏳ Menyimpan...</span>
        </button>
    </div>
    @endif

</div>

@script
<script>
    function processingTimer() {
        return {
            elapsed: '0:00',
            timerInterval: null,
            startTimer() {
                this.elapsed = '0:00';
                const start = Date.now();
                this.timerInterval = setInterval(() => {
                    const s = Math.floor((Date.now() - start) / 1000);
                    const m = Math.floor(s / 60);
                    this.elapsed = m + ':' + String(s % 60).padStart(2, '0');
                }, 1000);
            },
            stopTimer() {
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            }
        };
    }
</script>
@endscript