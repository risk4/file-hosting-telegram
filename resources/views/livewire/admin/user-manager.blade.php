<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold">Daftar Admin</h2>
            <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $users->count() }} user terdaftar</p>
        </div>
        <button wire:click="openCreate"
                class="px-4 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
            + Tambah User
        </button>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800 text-xs font-mono text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3 hidden md:table-cell">Login Terakhir</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($users as $user)
                <tr class="hover:bg-gray-800/40 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                <div class="text-xs text-teal-400 font-mono">(Anda)</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 font-mono text-xs text-gray-400">{{ $user->email }}</td>
                    <td class="px-4 py-4 text-xs font-mono text-gray-500 hidden md:table-cell">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                    </td>
                    <td class="px-4 py-4">
                        <button wire:click="toggleActive({{ $user->id }})"
                                class="text-xs font-mono px-2.5 py-1 rounded-lg transition-all
                                       {{ $user->is_active
                                          ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20'
                                          : 'bg-red-500/10 text-red-400 hover:bg-red-500/20' }}">
                            {{ $user->is_active ? '✅ Aktif' : '🔴 Nonaktif' }}
                        </button>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <button wire:click="openEdit({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-gray-500 hover:text-blue-400 hover:bg-blue-500/10 transition-colors">
                                ✏️
                            </button>
                            @if($user->id !== auth()->id())
                            <button wire:click="confirmDelete({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                🗑
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold">{{ $editingId ? '✏️ Edit User' : '+ User Baru' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Nama</label>
                    <input wire:model="name" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Email</label>
                    <input wire:model="email" type="email"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">
                        Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}
                    </label>
                    <input wire:model="password" type="password" placeholder="{{ $editingId ? '••••••• (opsional)' : 'Min. 6 karakter' }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" wire:model="isActive" class="rounded">
                    <span class="text-gray-300">Akun aktif</span>
                </label>
            </div>
            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-mono rounded-lg border border-gray-700 text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="save" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">
                    {{ $editingId ? 'Simpan' : 'Buat User' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirm --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-sm p-6 text-center">
            <div class="text-3xl mb-3">🗑️</div>
            <h3 class="font-semibold mb-2">Hapus User?</h3>
            <p class="text-sm text-gray-400 mb-5">Tindakan ini tidak bisa dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2 text-sm border border-gray-700 rounded-lg text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="deleteUser" class="flex-1 py-2 text-sm font-semibold bg-red-500 text-white rounded-lg hover:bg-red-400 transition-colors">Hapus</button>
            </div>
        </div>
    </div>
    @endif

</div>
