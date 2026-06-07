<div class="max-w-3xl space-y-6">

    {{-- ═══ TELEGRAM CONNECTION ═══ --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
            <span class="text-lg">✈️</span>
            <h2 class="font-semibold">Koneksi Telegram</h2>
            @if($telegramConnected)
            <span class="ml-auto flex items-center gap-1.5 text-xs font-mono text-teal-400">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>Terhubung
            </span>
            @endif
        </div>
        <div class="p-6">

            @if($loginStep === 'connected')
            {{-- Connected state --}}
            <div class="flex items-start gap-4 mb-6 p-4 bg-teal-500/5 border border-teal-500/20 rounded-xl">
                <div class="w-10 h-10 rounded-full bg-teal-500/20 flex items-center justify-center text-xl">👤</div>
                <div>
                    <div class="font-semibold">{{ ($telegramUser['firstName'] ?? '') . ' ' . ($telegramUser['lastName'] ?? '') }}</div>
                    @if($telegramUser['username'] ?? '')
                    <div class="text-sm text-gray-400 font-mono">@{{ $telegramUser['username'] }}</div>
                    @endif
                    @if($telegramUser['phone'] ?? '')
                    <div class="text-xs text-gray-500 font-mono">{{ $telegramUser['phone'] }}</div>
                    @endif
                </div>
            </div>

            <div class="space-y-4 mb-5">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Target Chat / Channel</label>
                    <input wire:model="telegramChatId" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    <p class="text-xs text-gray-500 font-mono mt-1">Gunakan: <code>me</code> (Saved Messages), <code>@username</code>, atau ID numerik channel</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="saveTelegramSettings" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
                    💾 Simpan Chat ID
                </button>
                <button wire:click="disconnectTelegram" class="px-4 py-2 text-sm font-mono rounded-lg border border-red-500/40 text-red-400 hover:bg-red-500/10 transition-colors">
                    🔌 Putus Koneksi
                </button>
            </div>

            @elseif($loginStep === 'credentials')
            {{-- Step 1: credentials --}}
            @if($loginMessage)
            <div class="mb-4 p-3 rounded-lg {{ str_starts_with($loginMessage, '✅') ? 'bg-teal-500/10 border border-teal-500/30 text-teal-300' : 'bg-red-500/10 border border-red-500/30 text-red-300' }} text-sm font-mono">
                {{ $loginMessage }}
            </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">API ID</label>
                    <input wire:model="telegramApiId" type="text" placeholder="12345678"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    <p class="text-xs text-gray-600 font-mono mt-1"><a href="https://my.telegram.org" target="_blank" class="text-teal-500 hover:underline">my.telegram.org</a> → API tools</p>
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">API Hash</label>
                    <input wire:model="telegramApiHash" type="password" placeholder="abc123..."
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                </div>
            </div>
            <div class="mb-5">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Nomor HP (+628xxx)</label>
                <input wire:model="loginPhone" type="tel" placeholder="+628123456789"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            </div>
            <button wire:click="startLogin" wire:loading.attr="disabled"
                    class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 disabled:opacity-50 transition-colors">
                <span wire:loading.remove>📱 Kirim Kode OTP</span>
                <span wire:loading>⏳ Mengirim...</span>
            </button>

            @elseif($loginStep === 'otp')
            {{-- Step 2: OTP --}}
            @if($loginMessage)
            <div class="mb-4 p-3 rounded-lg bg-teal-500/10 border border-teal-500/30 text-teal-300 text-sm font-mono">{{ $loginMessage }}</div>
            @endif
            <div class="mb-5">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Kode OTP dari Telegram</label>
                <input wire:model="loginOtp" type="text" placeholder="12345" maxlength="10"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            </div>
            <div class="flex gap-3">
                <button wire:click="$set('loginStep', 'credentials')" class="px-4 py-2 text-sm border border-gray-700 rounded-lg text-gray-400 hover:text-white transition-colors">← Kembali</button>
                <button wire:click="verifyOtp" wire:loading.attr="disabled"
                        class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 disabled:opacity-50 transition-colors">
                    <span wire:loading.remove>✅ Verifikasi</span>
                    <span wire:loading>⏳...</span>
                </button>
            </div>

            @elseif($loginStep === 'twofa')
            {{-- Step 3: 2FA --}}
            <div class="mb-4 p-3 rounded-lg bg-blue-500/10 border border-blue-500/30 text-blue-300 text-sm font-mono">🔒 Akun dilindungi 2FA</div>
            <div class="mb-5">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Password 2FA</label>
                <input wire:model="login2fa" type="password"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            </div>
            <button wire:click="verify2fa" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
                🔓 Login
            </button>
            @endif
        </div>
    </div>

    {{-- ═══ UPLOAD SETTINGS ═══ --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
            <span class="text-lg">☁️</span>
            <h2 class="font-semibold">Pengaturan Upload</h2>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Batas Upload Maksimal (MB)</label>
                    <input wire:model="maxUploadMb" type="number" min="1" max="2048"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    <p class="text-xs text-gray-600 font-mono mt-1">Maks 2048 MB (2 GB)</p>
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Label Upload Guest</label>
                    <input wire:model="guestUploadLabel" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    <p class="text-xs text-gray-600 font-mono mt-1">Nama yang tampil di kolom "Oleh"</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Ekstensi yang Diizinkan</label>
                <input wire:model="allowedExtensions" type="text" placeholder="jpg,png,pdf,mp4 (kosong = semua)"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" wire:model="guestUploadEnabled" class="rounded w-4 h-4">
                <div>
                    <div class="text-sm font-medium">Upload Guest Diaktifkan</div>
                    <div class="text-xs text-gray-500">Izinkan pengunjung publik untuk upload file</div>
                </div>
            </label>
            <button wire:click="saveUploadSettings" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
                💾 Simpan Pengaturan Upload
            </button>
        </div>
    </div>

    {{-- ═══ GENERAL SETTINGS ═══ --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
            <span class="text-lg">🌐</span>
            <h2 class="font-semibold">Pengaturan Umum</h2>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Nama Situs</label>
                    <input wire:model="siteName" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Deskripsi</label>
                    <input wire:model="siteDescription" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="publicBrowse" class="rounded w-4 h-4">
                    <div>
                        <div class="text-sm font-medium">Halaman Browse Publik</div>
                        <div class="text-xs text-gray-500">Pengunjung bisa melihat dan download file</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="publicUpload" class="rounded w-4 h-4">
                    <div>
                        <div class="text-sm font-medium">Halaman Upload Publik</div>
                        <div class="text-xs text-gray-500">Pengunjung bisa mengakses halaman upload</div>
                    </div>
                </label>
            </div>
            <button wire:click="saveGeneralSettings" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
                💾 Simpan Pengaturan Umum
            </button>
        </div>
    </div>

</div>
