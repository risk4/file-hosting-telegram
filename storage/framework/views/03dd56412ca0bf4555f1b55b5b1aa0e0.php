<div class="space-y-6">

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['label' => 'Total File',      'value' => number_format($stats['total_files']),     'icon' => '📁', 'color' => 'teal'],
            ['label' => 'Total Ukuran',    'value' => $this->formattedSize($stats['total_size']), 'icon' => '💾', 'color' => 'blue'],
            ['label' => 'Total Download',  'value' => number_format($stats['total_downloads']), 'icon' => '⬇️', 'color' => 'purple'],
            ['label' => 'Upload Hari Ini', 'value' => $stats['today_uploads'],                  'icon' => '🕐', 'color' => 'orange'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="text-2xl"><?php echo e($stat['icon']); ?></div>
                <span class="text-xs font-mono text-gray-600"><?php echo e($stat['label']); ?></span>
            </div>
            <div class="text-2xl font-bold font-mono text-<?php echo e($stat['color']); ?>-400"><?php echo e($stat['value']); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">📊 Breakdown Tipe File</h3>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['label' => 'Gambar',  'key' => 'images', 'icon' => '🖼️', 'color' => 'emerald'],
                    ['label' => 'Video',   'key' => 'videos', 'icon' => '🎬', 'color' => 'orange'],
                    ['label' => 'Dokumen', 'key' => 'docs',   'icon' => '📄', 'color' => 'blue'],
                    ['label' => 'Catatan', 'key' => 'total_notes', 'icon' => '📝', 'color' => 'yellow'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $total = max($stats['total_files'], 1); $pct = round($stats[$type['key']] / $total * 100); ?>
                <div>
                    <div class="flex justify-between text-xs font-mono text-gray-400 mb-1">
                        <span><?php echo e($type['icon']); ?> <?php echo e($type['label']); ?></span>
                        <span><?php echo e($stats[$type['key']]); ?></span>
                    </div>
                    <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-<?php echo e($type['color']); ?>-500 rounded-full" style="width: <?php echo e($pct); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                🔌 Status Sidecar (Node.js)
                <button wire:click="checkSidecar" class="ml-auto text-xs text-gray-500 hover:text-teal-400 font-mono">↻ Refresh</button>
            </h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sidecarStatus['connected'] ?? false): ?>
                <div class="flex items-center gap-2 text-teal-400 font-mono text-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    Terhubung
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($sidecarStatus['user'])): ?>
                <div class="text-xs text-gray-400 font-mono space-y-1">
                    <div>👤 <?php echo e($sidecarStatus['user']['firstName'] ?? ''); ?> <?php echo e($sidecarStatus['user']['lastName'] ?? ''); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sidecarStatus['user']['username'] ?? ''): ?>
                    <div>🔗 &#64;<?php echo e($sidecarStatus['user']['username']); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="flex items-center gap-2 text-red-400 font-mono text-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    Tidak terhubung
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($sidecarStatus['error'])): ?>
                <p class="text-xs text-gray-500 font-mono"><?php echo e($sidecarStatus['error']); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('admin.settings')); ?>" class="mt-3 inline-block text-xs text-teal-400 hover:underline font-mono">
                    → Konfigurasi di Pengaturan
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="font-semibold text-sm mb-4">📈 Statistik Lain</h3>
            <div class="space-y-3 text-sm font-mono">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['label' => 'Upload minggu ini',   'value' => $stats['week_uploads']],
                    ['label' => 'Upload oleh guest',   'value' => $stats['guest_uploads']],
                    ['label' => 'Catatan tersimpan',   'value' => $stats['total_notes']],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between text-gray-400">
                    <span><?php echo e($s['label']); ?></span>
                    <span class="text-white font-bold"><?php echo e($s['value']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <h3 class="font-semibold text-sm">🕐 File Terbaru</h3>
            <a href="<?php echo e(route('admin.files')); ?>" class="text-xs text-teal-400 hover:underline font-mono">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-800">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-4 px-6 py-3 hover:bg-gray-800/50 transition-colors">
                <span class="text-xl"><?php echo e($file['type'] === 'image' ? '🖼️' : ($file['type'] === 'video' ? '🎬' : ($file['type'] === 'note' ? '📝' : '📄'))); ?></span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate"><?php echo e($file['name']); ?></div>
                    <div class="text-xs text-gray-500 font-mono"><?php echo e($file['uploaded_by']); ?> · <?php echo e(\Carbon\Carbon::parse($file['created_at'])->diffForHumans()); ?></div>
                </div>
                <span class="text-xs font-mono text-gray-500">
                    <?php
                        $b = $file['size'];
                        echo $b < 1048576 ? round($b/1024,1).' KB' : round($b/1048576,1).' MB';
                    ?>
                </span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

</div>
<?php /**PATH C:\laragon\www\telestore-laravel\resources\views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>