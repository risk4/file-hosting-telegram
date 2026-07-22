<?php

namespace App\Livewire\Public;

use App\Models\TeleFile;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;

class FileBrowser extends Component
{
    use WithPagination;

    public function mount(): void
    {
        if (!Setting::get('public_browse_enabled', true)) {
            session()->flash('status', 'Halaman Browse publik sedang dinonaktifkan.');
            $this->redirectRoute('home');
        }
    }

    public string $search    = '';
    public string $type      = 'all';
    public string $category  = 'all';
    public string $sort      = 'newest';

    public ?TeleFile $previewFile = null;
    public bool $showPreview      = false;

    protected $queryString = [
        'search'   => ['except' => ''],
        'type'     => ['except' => 'all'],
        'category' => ['except' => 'all'],
        'sort'     => ['except' => 'newest'],
    ];

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingType(): void    { $this->resetPage(); }
    public function updatingCategory(): void { $this->resetPage(); }

    public function openPreview(int $id): void
    {
        $this->previewFile = TeleFile::public()->find($id);
        $this->showPreview = true;
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewFile = null;
    }

    public function render()
    {
        $query = TeleFile::public()->latest();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->type !== 'all') {
            $query->ofType($this->type);
        }

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        $query->orderBy(
            match($this->sort) {
                'oldest'    => 'created_at',
                'name'      => 'name',
                'size_desc' => 'size',
                'size_asc'  => 'size',
                'downloads' => 'download_count',
                default     => 'created_at',
            },
            in_array($this->sort, ['oldest', 'name', 'size_asc']) ? 'asc' : 'desc'
        );

        $files = $query->paginate(18);

        $categories = TeleFile::public()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $counts = [
            'all'   => TeleFile::public()->count(),
            'image' => TeleFile::public()->ofType('image')->count(),
            'video' => TeleFile::public()->ofType('video')->count(),
            'doc'   => TeleFile::public()->ofType('doc')->count(),
            'note'  => TeleFile::public()->ofType('note')->count(),
            'other' => TeleFile::public()->ofType('other')->count(),
        ];

        return view('livewire.public.file-browser', compact('files', 'categories', 'counts'))
            ->layout('layouts.public', ['title' => 'Browse File']);
    }
}
