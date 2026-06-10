<?php

namespace App\Livewire\Admin;

use App\Models\TeleFile;
use App\Services\TelegramService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class FileManager extends Component
{
    use WithPagination, WithFileUploads;

    // Filter
    public string $search   = '';
    public string $type     = 'all';
    public string $category = 'all';
    public string $sort     = 'newest';

    // Edit modal
    public bool       $showEditModal = false;
    public ?TeleFile  $editingFile   = null;
    public string     $editName      = '';
    public string     $editCategory  = '';
    public string     $editDesc      = '';
    public bool       $editPublic    = true;

    // Upload modal
    public bool  $showUploadModal = false;
    public $uploadFiles = [];
    public string $uploadCategory = 'uncategorized';
    public bool   $uploadPublic   = true;
    public bool   $uploading      = false;

    // Note modal
    public bool   $showNoteModal  = false;
    public string $noteContent    = '';
    public string $noteLabel      = '';
    public string $noteCategory   = 'uncategorized';

    // Delete confirm
    public bool      $showDeleteModal = false;
    public ?int      $deletingId      = null;
    public bool      $deleteFromTelegram = false;

    // View note
    public bool      $showNoteView  = false;
    public ?TeleFile $viewingNote   = null;

    protected $queryString = [
        'search'   => ['except' => ''],
        'type'     => ['except' => 'all'],
        'category' => ['except' => 'all'],
        'sort'     => ['except' => 'newest'],
    ];

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingType(): void     { $this->resetPage(); }
    public function updatingCategory(): void { $this->resetPage(); }

    // ── Upload ────────────────────────────────────────────
    public function processUploads(): void
    {
        $maxMb = (int) \App\Models\Setting::get('max_upload_mb', 2048);
        $maxKb = $maxMb * 1024; // Laravel validation 'max' for files uses kilobytes
        $this->validate([
            'uploadFiles.*' => "file|max:{$maxKb}",
        ]);

        $this->uploading = true;
        $telegram = app(TelegramService::class);
        $chatId   = \App\Models\Setting::get('telegram_chat_id', 'me');

        foreach ($this->uploadFiles as $file) {
            try {
                $origName = $file->getClientOriginalName();
                $mime     = $file->getMimeType();
                $result   = $telegram->uploadFile($file->getRealPath(), $origName, $chatId);

                TeleFile::create([
                    'uuid'                => Str::uuid(),
                    'name'                => pathinfo($origName, PATHINFO_FILENAME),
                    'original_name'       => $origName,
                    'type'                => TelegramService::detectType($origName, $mime),
                    'mime_type'           => $mime,
                    'size'                => $file->getSize(),
                    'category'            => $this->uploadCategory,
                    'telegram_message_id' => $result['messageId'],
                    'telegram_chat_id'    => $chatId,
                    'is_public'           => $this->uploadPublic,
                    'uploaded_by'         => auth()->user()->name,
                ]);
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: "Gagal: {$e->getMessage()}");
            }
        }

        $this->uploading      = false;
        $this->uploadFiles    = [];
        $this->showUploadModal = false;
        $this->dispatch('notify', type: 'success', message: 'Upload selesai');
    }

    // ── Save Note ─────────────────────────────────────────
    public function saveNote(): void
    {
        $this->validate([
            'noteContent' => 'required|string',
            'noteLabel'   => 'nullable|string|max:255',
        ]);

        $telegram = app(TelegramService::class);
        $chatId   = \App\Models\Setting::get('telegram_chat_id', 'me');
        $label    = $this->noteLabel ?: 'Catatan ' . now()->format('d/m/Y H:i');

        try {
            $result = $telegram->sendNote($chatId, $this->noteContent, $label);

            TeleFile::create([
                'uuid'                => Str::uuid(),
                'name'                => $label,
                'original_name'       => $label . '.txt',
                'type'                => 'note',
                'mime_type'           => 'text/plain',
                'size'                => strlen($this->noteContent),
                'category'            => $this->noteCategory,
                'telegram_message_id' => $result['messageId'],
                'telegram_chat_id'    => $chatId,
                'content'             => $this->noteContent,
                'is_public'           => true,
                'uploaded_by'         => auth()->user()->name,
            ]);

            $this->showNoteModal = false;
            $this->noteContent = $this->noteLabel = '';
            $this->dispatch('notify', type: 'success', message: 'Catatan disimpan');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // ── Edit ──────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $this->editingFile = TeleFile::findOrFail($id);
        $this->editName     = $this->editingFile->name;
        $this->editCategory = $this->editingFile->category;
        $this->editDesc     = $this->editingFile->description ?? '';
        $this->editPublic   = $this->editingFile->is_public;
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName'     => 'required|string|max:255',
            'editCategory' => 'nullable|string|max:100',
            'editDesc'     => 'nullable|string|max:1000',
        ]);

        $this->editingFile->update([
            'name'        => $this->editName,
            'category'    => $this->editCategory ?: 'uncategorized',
            'description' => $this->editDesc,
            'is_public'   => $this->editPublic,
        ]);

        $this->showEditModal = false;
        $this->dispatch('notify', type: 'success', message: 'File diperbarui');
    }

    // ── Delete ────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId           = $id;
        $this->deleteFromTelegram   = false;
        $this->showDeleteModal      = true;
    }

    public function deleteFile(): void
    {
        $file = TeleFile::findOrFail($this->deletingId);

        if ($this->deleteFromTelegram) {
            try {
                app(TelegramService::class)->deleteMessage(
                    $file->telegram_chat_id,
                    $file->telegram_message_id
                );
            } catch (\Exception $e) {
                // tetap hapus dari DB meski Telegram gagal
            }
        }

        $file->delete();
        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->dispatch('notify', type: 'success', message: 'File dihapus');
    }

    // ── View Note ─────────────────────────────────────────
    public function viewNote(int $id): void
    {
        $this->viewingNote  = TeleFile::findOrFail($id);
        $this->showNoteView = true;
    }

    public function render()
    {
        $query = TeleFile::query();

        if ($this->search)           $query->search($this->search);
        if ($this->type !== 'all')   $query->ofType($this->type);
        if ($this->category !== 'all') $query->where('category', $this->category);

        $query->orderBy(
            match($this->sort) {
                'oldest'    => 'created_at',
                'name'      => 'name',
                'size_desc' => 'size',
                'downloads' => 'download_count',
                default     => 'created_at',
            },
            $this->sort === 'oldest' || $this->sort === 'name' ? 'asc' : 'desc'
        );

        $files = $query->paginate(20);

        $categories = TeleFile::select('category')->distinct()->pluck('category')->filter()->sort()->values();

        $counts = [
            'all'   => TeleFile::count(),
            'image' => TeleFile::ofType('image')->count(),
            'video' => TeleFile::ofType('video')->count(),
            'doc'   => TeleFile::ofType('doc')->count(),
            'note'  => TeleFile::ofType('note')->count(),
            'other' => TeleFile::ofType('other')->count(),
        ];

        return view('livewire.admin.file-manager', compact('files', 'categories', 'counts'))
            ->layout('layouts.admin', ['title' => 'Manajemen File']);
    }
}
