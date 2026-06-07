<div class="space-y-5">

    
    <div class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-48">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">🔍</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari file..."
                   class="w-full bg-gray-900 border border-gray-800 rounded-lg pl-8 pr-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
        </div>
        <select wire:model.live="type" class="bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            <option value="all">Semua Tipe</option>
            <option value="image">🖼️ Gambar</option>
            <option value="video">🎬 Video</option>
            <option value="doc">📄 Dokumen</option>
            <option value="note">📝 Catatan</option>
            <option value="other">📦 Lainnya</option>
        </select>
        <select wire:model.live="sort" class="bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="name">Nama</option>
            <option value="size_desc">Ukuran</option>
            <option value="downloads">Download</option>
        </select>
        <div class="flex gap-2 ml-auto">
            <button wire:click="$set('showNoteModal', true)"
                    class="px-3 py-2 text-sm font-mono rounded-lg border border-gray-700 hover:border-yellow-500 hover:text-yellow-400 transition-colors">
                📝 Catatan
            </button>
            <button wire:click="$set('showUploadModal', true)"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-teal-500 text-black hover:bg-teal-400 transition-colors">
                ☁️ Upload
            </button>
        </div>
    </div>

    
    <div class="flex flex-wrap gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['v'=>'all','l'=>'Semua','k'=>'all'],['v'=>'image','l'=>'Gambar','k'=>'image'],
            ['v'=>'video','l'=>'Video','k'=>'video'],['v'=>'doc','l'=>'Dokumen','k'=>'doc'],
            ['v'=>'note','l'=>'Catatan','k'=>'note'],['v'=>'other','l'=>'Lainnya','k'=>'other'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button wire:click="$set('type','<?php echo e($t['v']); ?>')"
                class="px-3 py-1 text-xs font-mono rounded-lg border transition-all
                       <?php echo e($type === $t['v'] ? 'bg-teal-500/15 border-teal-500/40 text-teal-400' : 'bg-gray-900 border-gray-800 text-gray-500 hover:border-gray-600'); ?>">
            <?php echo e($t['l']); ?> (<?php echo e($counts[$t['k']]); ?>)
        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div wire:loading class="px-6 py-3 bg-teal-500/5 border-b border-teal-500/20 text-teal-400 text-xs font-mono">⏳ Memuat...</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800 text-xs font-mono text-gray-500 uppercase tracking-wider">
                        <th class="text-left px-5 py-3">File</th>
                        <th class="text-left px-4 py-3 hidden sm:table-cell">Tipe</th>
                        <th class="text-left px-4 py-3 hidden md:table-cell">Ukuran</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Kategori</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Upload</th>
                        <th class="text-left px-4 py-3 hidden xl:table-cell">Oleh</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-800/40 transition-colors group">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-lg flex-shrink-0"><?php echo e($file->icon); ?></span>
                                <div class="min-w-0">
                                    <div class="font-medium truncate max-w-xs"><?php echo e($file->name); ?></div>
                                    <div class="text-xs text-gray-500 font-mono truncate max-w-xs"><?php echo e($file->original_name); ?></div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$file->is_public): ?>
                                <span class="text-xs bg-orange-500/10 text-orange-400 border border-orange-500/20 px-1.5 py-0.5 rounded font-mono flex-shrink-0">Private</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-xs font-mono px-2 py-0.5 rounded
                                <?php echo e(match($file->type) {
                                    'image' => 'bg-emerald-500/10 text-emerald-400',
                                    'video' => 'bg-orange-500/10 text-orange-400',
                                    'doc'   => 'bg-blue-500/10 text-blue-400',
                                    'note'  => 'bg-yellow-500/10 text-yellow-400',
                                    default => 'bg-gray-700 text-gray-400',
                                }); ?>">
                                <?php echo e(strtoupper($file->type)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-400 hidden md:table-cell">
                            <?php echo e($file->formatted_size); ?>

                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500 hidden lg:table-cell">
                            <?php echo e($file->category); ?>

                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500 hidden lg:table-cell">
                            <?php echo e($file->created_at->format('d/m/y H:i')); ?>

                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500 hidden xl:table-cell">
                            <?php echo e($file->uploaded_by); ?>

                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file->type === 'note'): ?>
                                <button wire:click="viewNote(<?php echo e($file->id); ?>)"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-yellow-400 hover:bg-yellow-500/10 transition-colors" title="Lihat">
                                    👁
                                </button>
                                <?php else: ?>
                                <a href="<?php echo e(route('admin.file.download', $file->uuid)); ?>"
                                   class="p-1.5 rounded-lg text-gray-500 hover:text-teal-400 hover:bg-teal-500/10 transition-colors" title="Download">
                                    ⬇️
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div x-data="{ copied: false }" class="relative">
                                    <button type="button"
                                            x-on:click="navigator.clipboard.writeText('<?php echo e(route('file.download', $file->uuid)); ?>').then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-cyan-400 hover:bg-cyan-500/10 transition-colors" title="Salin link">
                                        <span x-text="copied ? '✔️' : '🔗'"></span>
                                    </button>
                                    <div x-show="copied" x-transition class="absolute -top-7 left-1/2 -translate-x-1/2 text-xs bg-gray-900 border border-gray-700 rounded-full px-2 py-1 text-teal-300 whitespace-nowrap">
                                        Tersalin!
                                    </div>
                                </div>
                                <button wire:click="openEdit(<?php echo e($file->id); ?>)"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-blue-400 hover:bg-blue-500/10 transition-colors" title="Edit">
                                    ✏️
                                </button>
                                <button wire:click="confirmDelete(<?php echo e($file->id); ?>)"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus">
                                    🗑
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-600 font-mono text-sm">
                            📭 Tidak ada file ditemukan
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-800">
            <?php echo e($files->links()); ?>

        </div>
    </div>

    

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showUploadModal): ?>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold">☁️ Upload File</h3>
                <button wire:click="$set('showUploadModal', false)" class="text-gray-500 hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Pilih File</label>
                    <input type="file" wire:model="uploadFiles" multiple
                           class="w-full text-sm font-mono text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-teal-500/10 file:text-teal-400 hover:file:bg-teal-500/20 cursor-pointer">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploadFiles.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Kategori</label>
                        <input wire:model="uploadCategory" type="text" placeholder="uncategorized"
                               class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" wire:model="uploadPublic" class="rounded">
                            <span class="text-gray-300">Tampilkan publik</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button wire:click="$set('showUploadModal', false)" class="px-4 py-2 text-sm font-mono rounded-lg border border-gray-700 text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="uploadFiles" wire:loading.attr="disabled"
                        class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 disabled:opacity-50 transition-colors">
                    <span wire:loading.remove>Upload</span>
                    <span wire:loading>⏳ Uploading...</span>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal && $editingFile): ?>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold">✏️ Edit File</h3>
                <button wire:click="$set('showEditModal', false)" class="text-gray-500 hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Nama</label>
                    <input wire:model="editName" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Kategori</label>
                    <input wire:model="editCategory" type="text"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Deskripsi</label>
                    <textarea wire:model="editDesc" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500 resize-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" wire:model="editPublic" class="rounded">
                    <span class="text-gray-300">Tampilkan di halaman publik</span>
                </label>
            </div>
            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button wire:click="$set('showEditModal', false)" class="px-4 py-2 text-sm font-mono rounded-lg border border-gray-700 text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="saveEdit" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">Simpan</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteModal): ?>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-sm p-6">
            <div class="text-3xl mb-3">🗑️</div>
            <h3 class="font-semibold mb-2">Hapus File?</h3>
            <p class="text-sm text-gray-400 mb-4">File akan dihapus dari database. Opsional: hapus juga dari Telegram.</p>
            <label class="flex items-center gap-2 text-sm mb-5 cursor-pointer">
                <input type="checkbox" wire:model="deleteFromTelegram" class="rounded">
                <span class="text-gray-300">Hapus juga dari Telegram</span>
            </label>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-2 text-sm border border-gray-700 rounded-lg text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="deleteFile" class="flex-1 py-2 text-sm font-semibold bg-red-500 text-white rounded-lg hover:bg-red-400 transition-colors">Hapus</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNoteView && $viewingNote): ?>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold"><?php echo e($viewingNote->name); ?></h3>
                <button wire:click="$set('showNoteView', false)" class="text-gray-500 hover:text-white">✕</button>
            </div>
            <div class="flex-1 overflow-auto p-6">
                <pre class="bg-gray-950 rounded-xl p-4 text-sm font-mono text-teal-300 whitespace-pre-wrap break-words"><?php echo e($viewingNote->content); ?></pre>
            </div>
            <div class="px-6 py-4 border-t border-gray-800">
                <button onclick="navigator.clipboard.writeText(`<?php echo e(addslashes($viewingNote->content ?? '')); ?>`)"
                        class="px-4 py-2 text-xs font-mono rounded-lg bg-gray-800 text-gray-300 hover:text-white transition-colors">
                    📋 Salin
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNoteModal): ?>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold">📝 Catatan Baru</h3>
                <button wire:click="$set('showNoteModal', false)" class="text-gray-500 hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Judul</label>
                    <input wire:model="noteLabel" type="text" placeholder="Nama catatan"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Isi</label>
                    <textarea wire:model="noteContent" rows="6"
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-teal-500 resize-y"
                              placeholder="Tulis catatan..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['noteContent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button wire:click="$set('showNoteModal', false)" class="px-4 py-2 text-sm font-mono rounded-lg border border-gray-700 text-gray-400 hover:text-white transition-colors">Batal</button>
                <button wire:click="saveNote" class="px-5 py-2 text-sm font-semibold bg-teal-500 text-black rounded-lg hover:bg-teal-400 transition-colors">Simpan</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH C:\laragon\www\telestore-laravel\resources\views/livewire/admin/file-manager.blade.php ENDPATH**/ ?>