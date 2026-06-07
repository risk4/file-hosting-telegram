<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

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

    {{-- Upload Results --}}
    @if(count($uploadResults) > 0)
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

    {{-- FILE TAB --}}
    @if($activeTab === 'file')
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

        {{-- Drop zone --}}
        <div x-data="{ dragging: false }"
             x-on:dragover.prevent="dragging = true"
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
                class="w-full py-3 bg-teal-500 hover:bg-teal-400 disabled:opacity-50 text-black font-bold rounded-xl transition-all">
            <span wire:loading.remove wire:target="uploadFiles">☁️ Upload ke Telegram</span>
            <span wire:loading wire:target="uploadFiles">⏳ Mengupload...</span>
        </button>
    </div>
    @endif

    {{-- NOTE TAB --}}
    @if($activeTab === 'note')
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
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
                class="w-full py-3 bg-teal-500 hover:bg-teal-400 disabled:opacity-50 text-black font-bold rounded-xl transition-all">
            <span wire:loading.remove wire:target="saveNote">💾 Simpan Catatan</span>
            <span wire:loading wire:target="saveNote">⏳ Menyimpan...</span>
        </button>
    </div>
    @endif

</div>
