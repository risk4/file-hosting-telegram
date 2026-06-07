<?php

namespace App\Livewire\Public;

use App\Models\TeleFile;
use App\Models\Setting;
use App\Services\TelegramService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

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

    protected function rules(): array
    {
        $maxMb = (int) Setting::get('max_upload_mb', 2048);
        return [
            'files.*'     => "file|max:{$maxMb}",
            'noteContent' => 'required_if:activeTab,note|string|max:50000',
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

        $this->validate(['files.*' => $this->rules()['files.*']]);

        $this->uploading = true;
        $telegram = app(TelegramService::class);
        $chatId   = Setting::get('telegram_chat_id', 'me');

        foreach ($this->files as $file) {
            try {
                $tmpPath  = $file->getRealPath();
                $origName = $file->getClientOriginalName();
                $mime     = $file->getMimeType();
                $size     = $file->getSize();
                $type     = TelegramService::detectType($origName, $mime);

                $result = $telegram->uploadFile($tmpPath, $origName, $chatId);

                $file = TeleFile::create([
                    'uuid'                 => Str::uuid(),
                    'name'                 => pathinfo($origName, PATHINFO_FILENAME),
                    'original_name'        => $origName,
                    'type'                 => $type,
                    'mime_type'            => $mime,
                    'size'                 => $size,
                    'category'             => $this->category ?: 'uncategorized',
                    'telegram_message_id'  => $result['messageId'],
                    'telegram_chat_id'     => $chatId,
                    'description'          => $this->description,
                    'is_public'            => true,
                    'uploaded_by'          => Setting::get('guest_upload_label', 'guest'),
                ]);

                $this->uploadResults[] = [
                    'name'   => $origName,
                    'status' => 'success',
                    'url'    => route('file.download', $file->uuid),
                ];
            } catch (\Exception $e) {
                $this->uploadResults[] = ['name' => $file->getClientOriginalName(), 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        $this->files      = [];
        $this->description = '';
        $this->uploading  = false;
        $this->dispatch('files-uploaded');
    }

    public function saveNote(): void
    {
        if (!Setting::get('guest_upload_enabled', true)) {
            $this->addError('noteContent', 'Upload sedang dinonaktifkan.');
            return;
        }

        $this->validate([
            'noteContent' => 'required|string|max:50000',
            'noteLabel'   => 'nullable|string|max:255',
        ]);

        $telegram = app(TelegramService::class);
        $chatId   = Setting::get('telegram_chat_id', 'me');
        $label    = $this->noteLabel ?: 'Catatan ' . now()->format('d/m/Y H:i');

        try {
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
