<?php

namespace App\Livewire\Public;

use App\Models\TeleFile;
use App\Models\Setting;
use App\Services\TelegramService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FileUpload extends Component
{
    use WithFileUploads;

    public function mount(): void
    {
        if (!Setting::get('public_upload_enabled', true)) {
            session()->flash('status', 'Halaman Upload publik sedang dinonaktifkan.');
            $this->redirectRoute('home');
        }
    }

    public $files = [];
    public string $noteContent = '';
    public string $noteLabel   = '';
    public string $category    = 'uncategorized';
    public string $description = '';
    public bool   $uploading   = false;
    public array  $uploadResults = [];
    public string $activeTab   = 'file'; // 'file' | 'note'

    // ── Processing queue & progress ─────────────────────
    public array  $processingQueue = [];
    public int    $currentFileIndex = 0;
    public int    $totalToProcess = 0;
    public bool   $isProcessing = false;
    public string $processingFileName = '';
    public string $pendingDir = '';

    protected function rules(): array
    {
        return [
            'files.*'     => 'nullable', // Flysystem can't access Livewire temp files reliably
            'noteContent' => 'required_if:activeTab,note|string',
            'noteLabel'   => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function uploadFiles(): void
    {
        if (!Setting::get('guest_upload_enabled', true)) {
            $this->addError('files', 'Upload file sedang dinonaktifkan oleh admin.');
            return;
        }

        if (empty($this->files)) {
            $this->addError('files', 'Pilih minimal satu file.');
            return;
        }

        // Move uploaded files to a persistent temp directory
        // so they survive across Livewire polling requests
        $uid = Str::random(16);
        $this->pendingDir = 'pending-uploads/' . $uid;
        $fullDir = storage_path('app/' . $this->pendingDir);
        File::makeDirectory($fullDir, 0755, true);

        $queue = [];
        foreach ($this->files as $file) {
            $origName = $file->getClientOriginalName();
            $destPath = $fullDir . '/' . $origName;
            // Use copy+unlink instead of move() — source is Livewire-managed
            // and may become unavailable before the request fully completes
            $source = $file->getRealPath();
            if ($source && copy($source, $destPath)) {
                @unlink($source);
            } else {
                $this->addError('files', "Gagal memindahkan file: " . $origName);
                continue;
            }
            // Read size & mime from the copied file, not the original UploadedFile,
            // because Livewire may clean up the temp file before polling starts
            $copiedSize = file_exists($destPath) ? filesize($destPath) : 0;
            $copiedMime = file_exists($destPath)
                ? (mime_content_type($destPath) ?: 'application/octet-stream')
                : 'application/octet-stream';
            $queue[] = [
                'path' => $destPath,
                'name' => $origName,
                'mime' => $copiedMime,
                'size' => $copiedSize,
            ];
        }

        if (empty($queue)) {
            $this->isProcessing = false;
            @File::deleteDirectory($fullDir);
            return;
        }

        $this->processingQueue = $queue;
        $this->totalToProcess  = count($queue);
        $this->currentFileIndex = 0;
        $this->isProcessing    = true;
        $this->processingFileName = '';
        $this->files = [];
    }

    /**
     * Process one file from the queue.
     * Called via wire:poll while isProcessing is true.
     */
    public function processNextFile(): void
    {
        if (!$this->isProcessing || $this->currentFileIndex >= $this->totalToProcess) {
            $this->isProcessing = false;
            return;
        }

        $item = $this->processingQueue[$this->currentFileIndex];
        $this->processingFileName = $item['name'];

        $telegram = app(TelegramService::class);
        $chatId   = Setting::get('telegram_chat_id', 'me');

        try {
            $result = $telegram->uploadFile($item['path'], $item['name'], $chatId);

            $type = TelegramService::detectType($item['name'], $item['mime']);

            $teleFile = TeleFile::create([
                'uuid'                 => Str::uuid(),
                'name'                 => pathinfo($item['name'], PATHINFO_FILENAME),
                'original_name'        => $item['name'],
                'type'                 => $type,
                'mime_type'            => $item['mime'],
                'size'                 => $item['size'],
                'category'             => $this->category ?: 'uncategorized',
                'telegram_message_id'  => $result['messageId'],
                'telegram_chat_id'     => $chatId,
                'description'          => $this->description,
                'is_public'            => true,
                'uploaded_by'          => Setting::get('guest_upload_label', 'guest'),
            ]);

            $this->uploadResults[] = [
                'name'   => $item['name'],
                'status' => 'success',
                'url'    => route('file.download', $teleFile->uuid),
            ];
        } catch (\Exception $e) {
            $this->uploadResults[] = [
                'name'    => $item['name'],
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // Clean up individual temp file
        if (file_exists($item['path'])) {
            @unlink($item['path']);
        }

        $this->currentFileIndex++;

        // All files processed
        if ($this->currentFileIndex >= $this->totalToProcess) {
            $this->isProcessing = false;
            $this->description  = '';

            // Clean up pending directory
            $dirPath = storage_path('app/' . $this->pendingDir);
            if (is_dir($dirPath)) {
                @File::deleteDirectory($dirPath);
            }
            $this->pendingDir = '';

            $this->dispatch('files-uploaded');
        }
    }

    public function saveNote(): void
    {
        if (!Setting::get('guest_upload_enabled', true)) {
            $this->addError('noteContent', 'Upload sedang dinonaktifkan.');
            return;
        }

        $this->validate([
            'noteContent' => 'required|string',
            'noteLabel'   => 'nullable|string|max:255',
        ]);

        $telegram = app(TelegramService::class);
        $chatId   = Setting::get('telegram_chat_id', 'me');
        $label    = $this->noteLabel ?: 'Catatan ' . now()->format('d/m/Y H:i');

        try {
            $result = $telegram->sendNote($chatId, $this->noteContent, $label);

            $teleFile = TeleFile::create([
                'uuid'                => Str::uuid(),
                'name'                => $label,
                'original_name'       => $label . '.txt',
                'type'                => 'note',
                'mime_type'           => 'text/plain',
                'size'                => strlen($this->noteContent),
                'category'            => $this->category ?: 'uncategorized',
                'telegram_message_id' => $result['messageId'],
                'telegram_chat_id'    => $chatId,
                'content'             => $this->noteContent,
                'is_public'           => true,
                'uploaded_by'         => Setting::get('guest_upload_label', 'guest'),
            ]);

            $this->uploadResults[] = [
                'name'   => $label,
                'status' => 'success',
                'url'    => route('file.download', $teleFile->uuid),
            ];
            $this->noteContent = '';
            $this->noteLabel   = '';
            $this->dispatch('files-uploaded');
        } catch (\Exception $e) {
            $this->addError('noteContent', $e->getMessage());
        }
    }

    public function clearResults(): void
    {
        $this->uploadResults = [];
    }

    public function render()
    {
        $categories = TeleFile::select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('livewire.public.file-upload', compact('categories'))
            ->layout('layouts.public', ['title' => 'Upload File']);
    }
}